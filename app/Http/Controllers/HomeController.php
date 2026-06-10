<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Plan;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $plans = Plan::query()
            ->where('status', Status::Active)
            ->orderBy('amount')
            ->get();

        $highlightPlanId = null;

        if ($plans->count() >= 2) {
            $highlightPlanId = $plans->get((int) floor($plans->count() / 2))?->id;
        }

        return view('home', [
            'plans' => $plans,
            'highlightPlanId' => $highlightPlanId,
        ]);
    }
}
