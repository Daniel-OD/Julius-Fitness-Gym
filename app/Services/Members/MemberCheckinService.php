<?php

namespace App\Services\Members;

use App\Data\CheckinScanResult;
use App\Enums\CheckinScanStatus;
use App\Models\CheckIn;
use App\Models\Member;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

class MemberCheckinService
{
    public function __construct(
        private MemberSubscriptionAccessService $subscriptionAccess,
    ) {}

    public function scan(string $token): CheckinScanResult
    {
        $member = Member::query()->where('checkin_token', $token)->first();

        if (! $member instanceof Member) {
            return new CheckinScanResult(
                status: CheckinScanStatus::Invalid,
                message: __('app.checkin.invalid'),
            );
        }

        if (! $this->subscriptionAccess->hasActiveSubscription($member)) {
            return new CheckinScanResult(
                status: CheckinScanStatus::Expired,
                memberName: $member->name,
                message: __('app.checkin.expired'),
            );
        }

        $checkedInAt = Carbon::now(AppConfig::timezone());

        MemberCheckin::query()->create([
            'member_id' => $member->id,
            'checked_in_at' => $checkedInAt,
        ]);

        return new CheckinScanResult(
            status: CheckinScanStatus::Success,
            memberName: $member->name,
            checkedInAt: $checkedInAt,
            message: __('app.checkin.success'),
        );
    }

    public function todayCount(): int
    {
        $today = CarbonImmutable::today(AppConfig::timezone());

        return CheckIn::query()
            ->whereBetween('checked_in_at', [
                $today->startOfDay(),
                $today->endOfDay(),
            ])
            ->count();
    }
}
