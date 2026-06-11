<?php

namespace App\Http\Controllers\Member;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Plan;
use App\Services\Members\MemberPlanSelectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MemberPlanController extends Controller
{
    public function __construct(private MemberPlanSelectionService $planSelection) {}

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
        /** @var Member $member */
        $member = auth('member')->user();

        $this->planSelection->select($member, $plan);

        return redirect()
            ->route('member.dashboard')
            ->with('success', __('app.member.plans.selected'));
    }
}
