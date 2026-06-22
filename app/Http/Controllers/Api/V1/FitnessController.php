<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\FoodLogStoreRequest;
use App\Http\Requests\Api\V1\WorkoutLogStoreRequest;
use App\Http\Resources\V1\ExerciseResource;
use App\Models\Exercise;
use App\Models\FoodLog;
use App\Models\Member;
use App\Models\NutritionPlan;
use App\Models\WorkoutLog;
use App\Models\WorkoutLogSet;
use App\Services\Api\QueryFilters;
use App\Services\Fitness\NutritionMacroCalculator;
use App\Services\Fitness\WorkoutPlanAssignmentService;
use App\Support\Fitness\InstructorAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FitnessController extends ApiController
{
    public function __construct(
        private readonly WorkoutPlanAssignmentService $workoutPlans,
        private readonly NutritionMacroCalculator $macros,
    ) {}

    public function exercises(Request $request): AnonymousResourceCollection
    {
        $this->requirePermission($request, 'ViewAny:Exercise');

        $query = Exercise::query()->where('is_active', true);
        QueryFilters::applyIndexFilters($query, $request, 'exercises');

        return ExerciseResource::collection($query->paginate(QueryFilters::perPage($request->query('per_page'))));
    }

    public function memberWorkoutPlan(Request $request, Member $member): JsonResponse
    {
        $this->requirePermission($request, 'View:MemberWorkoutPlan');
        $this->authorizeMemberAccess($request, $member);

        $plan = $this->workoutPlans->currentPlanForMember($member);

        abort_if($plan === null, 404);

        return response()->json([
            'data' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'start_date' => $plan->start_date->toDateString(),
                'end_date' => $plan->end_date?->toDateString(),
                'status' => $plan->status?->value,
                'days' => $plan->days->map(fn ($day): array => [
                    'id' => $day->id,
                    'day_number' => $day->day_number,
                    'name' => $day->name,
                    'exercises' => $day->exercises->map(fn ($item): array => [
                        'exercise_id' => $item->exercise_id,
                        'name' => $item->exercise?->name,
                        'sets' => $item->sets,
                        'reps' => $item->reps,
                        'duration_seconds' => $item->duration_seconds,
                        'rest_seconds' => $item->rest_seconds,
                        'order' => $item->order,
                        'notes' => $item->notes,
                    ])->values()->all(),
                ])->values()->all(),
            ],
        ]);
    }

    public function storeWorkoutLog(WorkoutLogStoreRequest $request): JsonResponse
    {
        $this->requirePermission($request, 'Create:WorkoutLog');

        $data = $request->validated();
        $member = Member::query()->findOrFail((int) $data['member_id']);
        $this->authorizeMemberAccess($request, $member);

        $log = WorkoutLog::query()->create([
            'member_id' => $member->id,
            'plan_day_id' => $data['plan_day_id'] ?? null,
            'logged_at' => $data['logged_at'] ?? now(),
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['sets'] ?? [] as $set) {
            WorkoutLogSet::query()->create(['log_id' => $log->id, ...$set]);
        }

        return response()->json(['data' => ['id' => $log->id]], 201);
    }

    public function memberNutritionPlan(Request $request, Member $member): JsonResponse
    {
        $this->requirePermission($request, 'View:NutritionPlan');
        $this->authorizeMemberAccess($request, $member);

        $today = now()->toDateString();
        $plan = NutritionPlan::query()
            ->with(['meals.items.foodItem'])
            ->where('member_id', $member->id)
            ->whereDate('start_date', '<=', $today)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today))
            ->latest('start_date')
            ->first();

        abort_if($plan === null, 404);

        return response()->json([
            'data' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'daily_calories' => $plan->daily_calories,
                'protein_g' => (float) $plan->protein_g,
                'carbs_g' => (float) $plan->carbs_g,
                'fat_g' => (float) $plan->fat_g,
                'meals' => $plan->meals->map(function ($meal): array {
                    $entries = $meal->items->map(fn ($item): array => [
                        'food_item' => $item->foodItem,
                        'quantity' => (float) $item->quantity,
                        'unit' => (string) $item->unit,
                    ])->all();

                    return [
                        'meal_type' => $meal->meal_type?->value,
                        'name' => $meal->name,
                        'items' => collect($entries)->map(fn (array $entry): array => [
                            'food_item_id' => $entry['food_item']->id,
                            'name' => $entry['food_item']->name,
                            'quantity' => $entry['quantity'],
                            'unit' => $entry['unit'],
                            'macros' => $this->macros->forFoodItem($entry['food_item'], $entry['quantity'], $entry['unit']),
                        ])->values()->all(),
                        'macros' => $this->macros->sum($entries),
                    ];
                })->values()->all(),
            ],
        ]);
    }

    public function storeFoodLog(FoodLogStoreRequest $request): JsonResponse
    {
        $this->requirePermission($request, 'Create:FoodLog');

        $data = $request->validated();
        $member = Member::query()->findOrFail((int) $data['member_id']);
        $this->authorizeMemberAccess($request, $member);

        $log = FoodLog::query()->create($data);

        return response()->json(['data' => ['id' => $log->id]], 201);
    }

    private function authorizeMemberAccess(Request $request, Member $member): void
    {
        abort_unless(InstructorAccess::canAccessMember($this->currentUser($request), $member), 403);
    }
}
