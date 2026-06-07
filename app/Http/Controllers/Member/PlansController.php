<?php

namespace App\Http\Controllers\Member;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Jobs\SendNewMemberNotification;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlansController extends Controller
{
    public function index(): View
    {
        $plans = Plan::where('status', Status::Active)->orderBy('amount')->get();

        return view('member.plans.index', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $member = auth('member')->user();
        $plan = Plan::where('status', Status::Active)->findOrFail($validated['plan_id']);

        Subscription::create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays($plan->days)->toDateString(),
        ]);

        SendNewMemberNotification::dispatch($member->id, $plan->id);

        return redirect()->route('member.plans')->with('success', __('app.member.plans.subscribed'));
    }
}
