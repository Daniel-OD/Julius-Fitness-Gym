<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $subscriptions = Subscription::query()
            ->with(['member', 'plan'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->member_id, fn ($q, $id) => $q->where('member_id', $id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('subscriptions.index', compact('subscriptions'));
    }

    public function create(Request $request): View
    {
        $members = Member::where('status', Status::Active)->orderBy('name')->get();
        $plans = Plan::where('status', Status::Active)->orderBy('name')->get();
        $selectedMember = $request->member_id ? Member::find($request->member_id) : null;

        return view('subscriptions.create', compact('members', 'plans', 'selectedMember'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'status' => ['required', 'in:upcoming,ongoing,expiring,expired,renewed,cancelled'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        if (empty($validated['end_date'])) {
            $validated['end_date'] = Carbon::parse($validated['start_date'])
                ->addDays((int) $plan->days)
                ->toDateString();
        }

        $subscription = Subscription::create($validated);

        return redirect()->route('web.subscriptions.show', $subscription)
            ->with('success', __('app.notifications.subscription_renewed_title'));
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['member', 'plan', 'invoices.transactions']);

        return view('subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription): View
    {
        $members = Member::orderBy('name')->get();
        $plans = Plan::where('status', Status::Active)->orderBy('name')->get();
        $subscription->load(['member', 'plan']);

        return view('subscriptions.edit', compact('subscription', 'members', 'plans'));
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'status' => ['required', 'in:upcoming,ongoing,expiring,expired,renewed,cancelled'],
        ]);

        $subscription->update($validated);

        return redirect()->route('web.subscriptions.show', $subscription)
            ->with('success', __('app.notifications.success'));
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        $subscription->delete();

        return redirect()->route('web.subscriptions.index')
            ->with('success', __('app.notifications.success'));
    }
}
