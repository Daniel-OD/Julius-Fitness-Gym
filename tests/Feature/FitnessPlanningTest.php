<?php

use App\Enums\WorkoutPlanStatus;
use App\Models\Exercise;
use App\Models\FoodItem;
use App\Models\Member;
use App\Models\MemberInstructorAssignment;
use App\Models\User;
use App\Models\WorkoutTemplate;
use App\Models\WorkoutTemplateExercise;
use App\Services\Fitness\NutritionMacroCalculator;
use App\Services\Fitness\WorkoutPlanAssignmentService;
use App\Support\Fitness\InstructorAccess;
use Database\Seeders\FoodItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('calculates macros per 100g accurately', function (): void {
    $food = FoodItem::factory()->create([
        'calories_per_100g' => 165,
        'protein' => 31,
        'carbs' => 0,
        'fat' => 3.6,
        'fiber' => 0,
        'serving_size' => 100,
    ]);

    $macros = app(NutritionMacroCalculator::class)->forFoodItem($food, 150, 'g');

    expect($macros['calories'])->toBe(247.5)
        ->and($macros['protein'])->toBe(46.5)
        ->and($macros['fat'])->toBe(5.4);
});

it('calculates macros for servings using serving size', function (): void {
    $food = FoodItem::factory()->create([
        'calories_per_100g' => 100,
        'protein' => 10,
        'carbs' => 10,
        'fat' => 5,
        'fiber' => 2,
        'serving_size' => 50,
    ]);

    $macros = app(NutritionMacroCalculator::class)->forFoodItem($food, 2, 'serving');

    expect($macros['calories'])->toBe(100.0)
        ->and($macros['protein'])->toBe(10.0);
});

it('assigns workout template to member and copies exercises', function (): void {
    $member = Member::factory()->create();
    $exercise = Exercise::factory()->create();
    $template = WorkoutTemplate::factory()->create();

    WorkoutTemplateExercise::query()->create([
        'template_id' => $template->id,
        'exercise_id' => $exercise->id,
        'sets' => 3,
        'reps' => 10,
        'rest_seconds' => 60,
        'order' => 1,
    ]);

    $plan = app(WorkoutPlanAssignmentService::class)->assign($member, [
        'name' => 'Strength block',
        'start_date' => now()->toDateString(),
        'template_id' => $template->id,
    ]);

    expect($plan->days)->toHaveCount(1)
        ->and($plan->days->first()->exercises)->toHaveCount(1)
        ->and($plan->days->first()->exercises->first()->sets)->toBe(3);
});

it('deactivates previous active plans when assigning a new one', function (): void {
    $member = Member::factory()->create();

    $first = app(WorkoutPlanAssignmentService::class)->assign($member, [
        'name' => 'Plan A',
        'start_date' => now()->toDateString(),
        'days' => [['day_number' => 1, 'name' => 'Day 1']],
    ]);

    app(WorkoutPlanAssignmentService::class)->assign($member, [
        'name' => 'Plan B',
        'start_date' => now()->toDateString(),
        'days' => [['day_number' => 1, 'name' => 'Day 1']],
    ]);

    expect($first->fresh()->status)->toBe(WorkoutPlanStatus::Completed)
        ->and($member->workoutPlans()->where('status', WorkoutPlanStatus::Active)->count())->toBe(1);
});

it('restricts instructor access to assigned members only', function (): void {
    Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);

    $instructor = User::factory()->create();
    $instructor->assignRole('instructor');

    $assigned = Member::factory()->create();
    $other = Member::factory()->create();

    MemberInstructorAssignment::query()->create([
        'member_id' => $assigned->id,
        'instructor_id' => $instructor->id,
    ]);

    expect(InstructorAccess::canAccessMember($instructor, $assigned))->toBeTrue()
        ->and(InstructorAccess::canAccessMember($instructor, $other))->toBeFalse();
});

it('seeds at least 50 common food items', function (): void {
    $this->seed(FoodItemSeeder::class);

    expect(FoodItem::query()->count())->toBeGreaterThanOrEqual(50);
});

it('member can view workout plan page', function (): void {
    $member = Member::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($member, 'member')
        ->get(route('member.fitness.workout-plan'))
        ->assertSuccessful();
});
