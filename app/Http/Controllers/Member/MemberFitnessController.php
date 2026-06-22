<?php

namespace App\Http\Controllers\Member;

use App\Enums\MealType;
use App\Http\Controllers\Controller;
use App\Models\FoodItem;
use App\Models\FoodLog;
use App\Models\Member;
use App\Models\NutritionPlan;
use App\Models\WorkoutLog;
use App\Models\WorkoutLogSet;
use App\Services\Fitness\NutritionMacroCalculator;
use App\Services\Fitness\WorkoutPlanAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberFitnessController extends Controller
{
    public function __construct(
        private readonly WorkoutPlanAssignmentService $workoutPlans,
        private readonly NutritionMacroCalculator $macros,
    ) {}

    public function workoutPlan(): View
    {
        /** @var Member $member */
        $member = auth('member')->user();
        $plan = $this->workoutPlans->currentPlanForMember($member);
        $todayDayNumber = ((int) now()->dayOfWeekIso) % 7 + 1;
        $today = $plan?->days->firstWhere('day_number', $todayDayNumber) ?? $plan?->days->first();

        return view('member.fitness.workout-plan', compact('plan', 'today', 'todayDayNumber'));
    }

    public function logWorkout(Request $request): RedirectResponse
    {
        /** @var Member $member */
        $member = auth('member')->user();

        $validated = $request->validate([
            'plan_day_id' => ['nullable', 'integer', 'exists:workout_plan_days,id'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'sets' => ['nullable', 'array'],
            'sets.*.exercise_id' => ['required', 'integer', 'exists:exercise_library,id'],
            'sets.*.set_number' => ['required', 'integer', 'min:1'],
            'sets.*.reps' => ['nullable', 'integer', 'min:0'],
            'sets.*.weight' => ['nullable', 'numeric', 'min:0'],
            'sets.*.duration_seconds' => ['nullable', 'integer', 'min:0'],
            'sets.*.rest_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $log = WorkoutLog::query()->create([
            'member_id' => $member->id,
            'plan_day_id' => $validated['plan_day_id'] ?? null,
            'logged_at' => now(),
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['sets'] ?? [] as $set) {
            WorkoutLogSet::query()->create([
                'log_id' => $log->id,
                ...$set,
            ]);
        }

        return redirect()->route('member.fitness.workout-plan')->with('success', __('app.fitness.workout_logged'));
    }

    public function nutritionPlan(): View
    {
        /** @var Member $member */
        $member = auth('member')->user();
        $plan = $this->currentNutritionPlan($member);

        return view('member.fitness.nutrition-plan', compact('plan'));
    }

    public function foodLog(Request $request): View
    {
        /** @var Member $member */
        $member = auth('member')->user();
        $date = $request->date('date') ?? today();
        $logs = FoodLog::query()
            ->with('foodItem')
            ->where('member_id', $member->id)
            ->whereDate('logged_at', $date)
            ->get();

        $entries = $logs->map(fn (FoodLog $log): array => [
            'food_item' => $log->foodItem,
            'quantity' => (float) $log->quantity,
            'unit' => (string) $log->unit,
        ])->all();

        $totals = $this->macros->sum($entries);
        $plan = $this->currentNutritionPlan($member);
        $foods = FoodItem::query()->orderBy('name')->limit(200)->get();

        return view('member.fitness.food-log', compact('logs', 'totals', 'plan', 'foods', 'date'));
    }

    public function storeFoodLog(Request $request): RedirectResponse
    {
        /** @var Member $member */
        $member = auth('member')->user();

        $validated = $request->validate([
            'logged_at' => ['required', 'date'],
            'meal_type' => ['required', 'string'],
            'food_item_id' => ['required', 'integer', 'exists:food_items,id'],
            'quantity' => ['required', 'numeric', 'min:0.1'],
            'unit' => ['required', 'string', 'max:20'],
        ]);

        FoodLog::query()->create([
            'member_id' => $member->id,
            'logged_at' => $validated['logged_at'],
            'meal_type' => MealType::from($validated['meal_type']),
            'food_item_id' => $validated['food_item_id'],
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
        ]);

        return redirect()->route('member.fitness.food-log', ['date' => $validated['logged_at']])
            ->with('success', __('app.fitness.food_logged'));
    }

    private function currentNutritionPlan(Member $member): ?NutritionPlan
    {
        $today = now()->toDateString();

        return NutritionPlan::query()
            ->with(['meals.items.foodItem'])
            ->where('member_id', $member->id)
            ->whereDate('start_date', '<=', $today)
            ->where(function ($query) use ($today): void {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->latest('start_date')
            ->first();
    }
}
