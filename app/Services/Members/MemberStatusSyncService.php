<?php

namespace App\Services\Members;

use App\Enums\Status;
use App\Models\Member;
use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Keeps members.status in line with subscription reality: a member is
 * "active" while they hold a subscription that is valid today, and
 * "inactive" otherwise. Used by the SubscriptionObserver (immediate),
 * the gym:subscriptions daily command and the Excel import (bulk healing
 * for imported / restored databases).
 */
class MemberStatusSyncService
{
    /**
     * Subscription statuses that never grant an active membership,
     * regardless of their date range.
     *
     * @var list<string>
     */
    public const NON_QUALIFYING_STATUSES = ['cancelled', 'renewed', 'pending_payment', 'expired'];

    /**
     * Re-bucket every member; also heals NULL or unknown status values.
     *
     * @return array{activated: int, deactivated: int}
     */
    public function syncAll(): array
    {
        $activated = Member::query()
            ->where(function (Builder $query): void {
                $query->whereNull('status')
                    ->orWhere('status', '!=', Status::Active->value);
            })
            ->whereHas('subscriptions', fn (Builder $query) => $this->applyCurrentlyValid($query))
            ->update(['status' => Status::Active->value]);

        $deactivated = Member::query()
            ->where(function (Builder $query): void {
                $query->whereNull('status')
                    ->orWhere('status', '!=', Status::Inactive->value);
            })
            ->whereDoesntHave('subscriptions', fn (Builder $query) => $this->applyCurrentlyValid($query))
            ->update(['status' => Status::Inactive->value]);

        return ['activated' => $activated, 'deactivated' => $deactivated];
    }

    public function syncMember(Member $member): void
    {
        $hasCurrentSubscription = $this->applyCurrentlyValid(
            $member->subscriptions()->getQuery(),
        )->exists();

        $target = $hasCurrentSubscription ? Status::Active : Status::Inactive;

        if ($member->status !== $target) {
            $member->forceFill(['status' => $target])->save();
        }
    }

    /**
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    private function applyCurrentlyValid(Builder $query): Builder
    {
        $today = CarbonImmutable::today(AppConfig::timezone())->toDateString();

        return $query
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereNotIn('status', self::NON_QUALIFYING_STATUSES);
    }
}
