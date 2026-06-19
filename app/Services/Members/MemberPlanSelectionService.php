<?php

namespace App\Services\Members;

use App\Enums\Status;
use App\Helpers\Helpers;
use App\Jobs\SendNewMemberNotification;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Support\AppConfig;
use App\Support\MemberPlanIntent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MemberPlanSelectionService
{
    public function __construct(private readonly MemberPlanIntent $planIntent) {}

    public function select(Member $member, Plan $plan): Subscription
    {
        abort_unless($plan->status === Status::Active, 404);

        $subscription = DB::transaction(function () use ($member, $plan): Subscription {
            $timezone = AppConfig::timezone();
            $today = Carbon::today($timezone)->toDateString();
            $endDate = Helpers::calculateSubscriptionEndDate($today, $plan->id);

            $subscription = Subscription::create([
                'member_id' => $member->id,
                'plan_id' => $plan->id,
                'start_date' => $today,
                'end_date' => $endDate,
                'status' => Status::PendingPayment->value,
            ]);

            Invoice::create([
                'subscription_id' => $subscription->id,
                'date' => $today,
                'due_date' => $today,
                'subscription_fee' => round((float) $plan->amount, 2),
                'paid_amount' => 0,
                'status' => Status::Issued->value,
            ]);

            return $subscription;
        });

        SendNewMemberNotification::dispatch($member->id, $plan->id);
        $this->planIntent->forget();

        return $subscription;
    }

    public function fulfillPending(Member $member): ?Subscription
    {
        if ($member->subscriptions()->exists()) {
            $this->planIntent->forget();

            return null;
        }

        $plan = $this->planIntent->resolvePlan();

        if ($plan === null) {
            return null;
        }

        return $this->select($member, $plan);
    }
}
