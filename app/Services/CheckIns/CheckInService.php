<?php

namespace App\Services\CheckIns;

use App\Helpers\Helpers;
use App\Models\CheckIn;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class CheckInService
{
    private const DEFAULT_PRESENT_NOW_GRACE_MINUTES = 15;

    private const MAX_PRESENT_NOW_GRACE_MINUTES = 120;

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
            ->whereBetween('checked_in_at', [$now->startOfDay(), $now->endOfDay()])
            ->count();
    }
}
