<?php

namespace App\Http\Controllers\Member;

use App\Enums\Status;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Jobs\SendNewMemberNotification;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberPlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::query()
            ->where('status', Status::Active)
            ->orderBy('amount')
            ->get();

        return view('member.plans.index', compact('plans'));
    }

    public function select(Plan $plan): RedirectResponse
    {
        abort_unless($plan->status === Status::Active, 404);

        /** @var Member $member */
        $member = auth('member')->user();

        DB::transaction(function () use ($member, $plan): void {
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
        });

        SendNewMemberNotification::dispatch($member->id, $plan->id);

        return redirect()
            ->route('member.dashboard')
            ->with('success', __('app.member.plans.selected'));
    }
}
