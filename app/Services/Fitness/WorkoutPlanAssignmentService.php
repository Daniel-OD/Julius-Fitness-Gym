<?php

namespace App\Services\Fitness;

use App\Enums\WorkoutPlanStatus;
use App\Models\Member;
use App\Models\MemberWorkoutPlan;
use App\Models\User;
use App\Models\WorkoutPlanDay;
use App\Models\WorkoutPlanExercise;
use App\Models\WorkoutTemplate;
use Illuminate\Support\Facades\DB;

/**
 * Assigns workout templates to members and builds plan days/exercises.
 */
final class WorkoutPlanAssignmentService
{
    /**
     * @param  array{
     *   name: string,
     *   start_date: string,
     *   end_date?: string|null,
     *   notes?: string|null,
     *   template_id?: int|null,
     *   days?: list<array{day_number: int, name?: string|null, template_id?: int|null, notes?: string|null}>
     * }  $data
     */
    public function assign(Member $member, array $data, ?User $assigner = null): MemberWorkoutPlan
    {
        return DB::transaction(function () use ($member, $data, $assigner): MemberWorkoutPlan {
            $this->deactivateExistingPlans($member);

            $plan = MemberWorkoutPlan::query()->create([
                'member_id' => $member->id,
                'assigned_by' => $assigner?->id,
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => WorkoutPlanStatus::Active,
            ]);

            $days = $data['days'] ?? [];

            if ($days === [] && ! empty($data['template_id'])) {
                $days = [[
                    'day_number' => 1,
                    'name' => __('app.fitness.default_day_name'),
                    'template_id' => (int) $data['template_id'],
                ]];
            }

            foreach ($days as $dayData) {
                $this->createPlanDay($plan, $dayData);
            }

            return $plan->fresh(['days.exercises.exercise', 'member']);
        });
    }

    /**
     * @param  array{day_number: int, name?: string|null, template_id?: int|null, notes?: string|null}  $dayData
     */
    public function createPlanDay(MemberWorkoutPlan $plan, array $dayData): WorkoutPlanDay
    {
        $day = WorkoutPlanDay::query()->create([
            'plan_id' => $plan->id,
            'day_number' => (int) $dayData['day_number'],
            'name' => $dayData['name'] ?? null,
            'template_id' => $dayData['template_id'] ?? null,
            'notes' => $dayData['notes'] ?? null,
        ]);

        if (! empty($dayData['template_id'])) {
            $template = WorkoutTemplate::query()
                ->with('exercises')
                ->find((int) $dayData['template_id']);

            if ($template) {
                foreach ($template->exercises as $templateExercise) {
                    WorkoutPlanExercise::query()->create([
                        'plan_day_id' => $day->id,
                        'exercise_id' => $templateExercise->exercise_id,
                        'sets' => $templateExercise->sets,
                        'reps' => $templateExercise->reps,
                        'duration_seconds' => $templateExercise->duration_seconds,
                        'rest_seconds' => $templateExercise->rest_seconds,
                        'order' => $templateExercise->order,
                        'notes' => $templateExercise->notes,
                    ]);
                }
            }
        }

        return $day->fresh(['exercises.exercise']);
    }

    public function currentPlanForMember(Member $member): ?MemberWorkoutPlan
    {
        $today = now()->toDateString();

        return MemberWorkoutPlan::query()
            ->with(['days.exercises.exercise'])
            ->where('member_id', $member->id)
            ->where('status', WorkoutPlanStatus::Active)
            ->whereDate('start_date', '<=', $today)
            ->where(function ($query) use ($today): void {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->latest('start_date')
            ->first();
    }

    private function deactivateExistingPlans(Member $member): void
    {
        MemberWorkoutPlan::query()
            ->where('member_id', $member->id)
            ->where('status', WorkoutPlanStatus::Active)
            ->update(['status' => WorkoutPlanStatus::Completed]);
    }
}
