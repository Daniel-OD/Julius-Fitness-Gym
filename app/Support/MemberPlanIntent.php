<?php

namespace App\Support;

use App\Enums\Status;
use App\Models\Plan;
use Illuminate\Http\Request;

final class MemberPlanIntent
{
    public const string SESSION_KEY = 'member.intended_plan_id';

    public function __construct(private Request $request) {}

    public function store(int $planId): void
    {
        if ($planId <= 0) {
            return;
        }

        $this->request->session()->put(self::SESSION_KEY, $planId);
    }

    public function forget(): void
    {
        $this->request->session()->forget(self::SESSION_KEY);
    }

    public function id(): ?int
    {
        $planId = $this->request->session()->get(self::SESSION_KEY);

        return is_int($planId) ? $planId : (is_numeric($planId) ? (int) $planId : null);
    }

    public function resolvePlan(): ?Plan
    {
        $planId = $this->id();

        if ($planId === null) {
            return null;
        }

        $plan = Plan::query()
            ->whereKey($planId)
            ->where('status', Status::Active)
            ->first();

        if ($plan === null) {
            $this->forget();

            return null;
        }

        return $plan;
    }
}
