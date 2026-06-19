<?php

namespace App\Services\CheckIns;

use App\Enums\CheckInStatus;
use App\Helpers\Helpers;
use App\Jobs\SendGraceEntryNotification;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\RateLimiter;

class CheckInService
{
    private const int DEFAULT_PRESENT_NOW_GRACE_MINUTES = 15;

    private const int MAX_PRESENT_NOW_GRACE_MINUTES = 120;

    private const int RATE_LIMIT_MINUTES = 30;

    /**
     * Handle a QR scan end-to-end: resolve the member by token, decide the
     * outcome (success / grace entry / blocked) and persist the attempt.
     *
     * Grace rule: a member whose latest subscription has expired is allowed
     * one warning entry per expiry period; every later scan is blocked until
     * the subscription is renewed.
     */
    public function recordScan(string $qrToken): CheckInResult
    {
        $settings = Helpers::getSettings();

        if (! (bool) data_get($settings, 'checkin.enabled', true)) {
            return new CheckInResult('error', __('app.checkin.disabled'), 503);
        }

        $member = Member::where('checkin_token', $qrToken)->first();

        if (! $member) {
            return new CheckInResult('error', __('app.checkin.invalid_token'), 404);
        }

        if ($this->hasOpenSession($member->id)) {
            return new CheckInResult(
                'already_present',
                __('app.checkin.already_present', ['name' => $member->name]),
                422,
                $member,
            );
        }

        $rateLimitKey = "checkin:{$member->id}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return new CheckInResult(
                'rate_limited',
                __('app.checkin.rate_limited', ['minutes' => ceil($seconds / 60)]),
                429,
                $member,
            );
        }

        $now = Carbon::now(AppConfig::timezone());
        $subscription = $this->activeSubscriptionFor($member->id, $now);

        if ($subscription) {
            $checkIn = $this->storeEntry($member, $subscription, $now, CheckInStatus::Success);
            RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_MINUTES * 60);

            return new CheckInResult(
                'success',
                __('app.checkin.success', ['name' => $member->name]),
                200,
                $member,
                $subscription,
                $checkIn,
            );
        }

        $latestSubscription = $this->latestSubscriptionFor($member->id);

        if (! $latestSubscription) {
            $checkIn = $this->storeDenied($member, $now, 'no_subscription');

            return new CheckInResult(
                'blocked',
                __('app.checkin.no_active_subscription'),
                422,
                $member,
                null,
                $checkIn,
            );
        }

        $requireActive = (bool) data_get($settings, 'checkin.require_active_subscription', false);

        if ($requireActive) {
            $checkIn = $this->storeDenied($member, $now, 'no_subscription');

            return new CheckInResult(
                'blocked',
                __('app.checkin.no_active_subscription'),
                422,
                $member,
                null,
                $checkIn,
            );
        }

        if ($this->graceEntryUsedSince($member->id, $latestSubscription->end_date)) {
            $checkIn = $this->storeDenied($member, $now, 'expired_grace_used');

            return new CheckInResult(
                'blocked',
                __('app.checkin.blocked_expired', ['name' => $member->name]),
                422,
                $member,
                null,
                $checkIn,
            );
        }

        $checkIn = $this->storeEntry($member, null, $now, CheckInStatus::GraceEntry);
        RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_MINUTES * 60);

        SendGraceEntryNotification::dispatch($checkIn->id);

        return new CheckInResult(
            'grace_entry',
            __('app.checkin.grace_entry', ['name' => $member->name]),
            200,
            $member,
            null,
            $checkIn,
        );
    }

    /**
     * The member's currently valid subscription, if any.
     */
    public function activeSubscriptionFor(int $memberId, ?Carbon $now = null): ?Subscription
    {
        $today = ($now ?? Carbon::now(AppConfig::timezone()))->toDateString();

        return Subscription::query()
            ->where('member_id', $memberId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereNotIn('status', ['cancelled', 'renewed'])
            ->latest('end_date')
            ->first();
    }

    private function latestSubscriptionFor(int $memberId): ?Subscription
    {
        return Subscription::query()
            ->where('member_id', $memberId)
            ->whereNotIn('status', ['cancelled', 'renewed'])
            ->latest('end_date')
            ->first();
    }

    /**
     * Whether the member already consumed the single grace entry for the
     * current expiry period (i.e. after the latest subscription ended).
     */
    private function graceEntryUsedSince(int $memberId, Carbon $expiredOn): bool
    {
        return CheckIn::query()
            ->where('member_id', $memberId)
            ->where('status', CheckInStatus::GraceEntry)
            ->where('checked_in_at', '>', $expiredOn->copy()->endOfDay())
            ->exists();
    }

    private function storeEntry(Member $member, ?Subscription $subscription, Carbon $now, CheckInStatus $status): CheckIn
    {
        return CheckIn::create([
            'member_id' => $member->id,
            'subscription_id' => $subscription?->id,
            'checked_in_at' => $now,
            'status' => $status,
            'method' => 'qr',
        ]);
    }

    /**
     * Log a denied attempt for the audit trail; blocked rows never count as
     * presence and are excluded from every presence query.
     */
    private function storeDenied(Member $member, Carbon $now, string $reason): CheckIn
    {
        return CheckIn::create([
            'member_id' => $member->id,
            'subscription_id' => null,
            'checked_in_at' => $now,
            'checked_out_at' => $now,
            'status' => CheckInStatus::Blocked,
            'denied_reason' => $reason,
            'method' => 'qr',
        ]);
    }

    /**
     * Minutes to keep a checked-out member visible on "Present now".
     */
    public function presentNowGraceMinutes(): int
    {
        $minutes = (int) data_get(
            Helpers::getSettings(),
            'checkin.present_now_grace_minutes',
            self::DEFAULT_PRESENT_NOW_GRACE_MINUTES,
        );

        return max(0, min($minutes, self::MAX_PRESENT_NOW_GRACE_MINUTES));
    }

    /**
     * @return Builder<CheckIn>
     */
    public function openSessionQuery(int $memberId): Builder
    {
        return CheckIn::query()
            ->where('member_id', $memberId)
            ->where('status', '!=', CheckInStatus::Blocked)
            ->whereNull('checked_out_at');
    }

    public function hasOpenSession(int $memberId): bool
    {
        return $this->openSessionQuery($memberId)->exists();
    }

    /**
     * Members considered present today: no checkout, or checked out within the grace window.
     *
     * @return Builder<CheckIn>
     */
    public function presentNowQuery(): Builder
    {
        $timezone = AppConfig::timezone();
        $now = CarbonImmutable::now($timezone);
        $graceCutoff = $now
            ->subMinutes($this->presentNowGraceMinutes())
            ->toDateTimeString();

        return CheckIn::query()
            ->with('member')
            ->where('status', '!=', CheckInStatus::Blocked)
            ->whereBetween('checked_in_at', [$now->startOfDay(), $now->endOfDay()])
            ->where(function (Builder $query) use ($graceCutoff): void {
                $query
                    ->whereNull('checked_out_at')
                    ->orWhere('checked_out_at', '>=', $graceCutoff);
            })
            ->latest('checked_in_at');
    }

    public function todayCheckInCount(): int
    {
        $now = CarbonImmutable::now(AppConfig::timezone());

        return CheckIn::query()
            ->where('status', '!=', CheckInStatus::Blocked)
            ->whereBetween('checked_in_at', [$now->startOfDay(), $now->endOfDay()])
            ->count();
    }
}
