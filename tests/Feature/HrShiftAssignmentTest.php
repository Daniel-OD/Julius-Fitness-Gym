<?php

use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('isValidOn correctly classifies active, upcoming, and expired assignments', function (): void {
    $user = User::factory()->create();
    $shift = Shift::factory()->create();

    $active = ShiftAssignment::create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'valid_from' => Carbon::parse('2026-01-01'),
        'valid_until' => Carbon::parse('2026-12-31'),
    ]);

    $upcoming = ShiftAssignment::create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'valid_from' => Carbon::parse('2027-01-01'),
        'valid_until' => null,
    ]);

    $expired = ShiftAssignment::create([
        'user_id' => $user->id,
        'shift_id' => $shift->id,
        'valid_from' => Carbon::parse('2025-01-01'),
        'valid_until' => Carbon::parse('2025-06-30'),
    ]);

    $today = Carbon::parse('2026-06-15');

    expect($active->isValidOn($today))->toBeTrue()
        ->and($upcoming->isValidOn($today))->toBeFalse()
        ->and($expired->isValidOn($today))->toBeFalse();
});

it('shiftAssignments relation on StaffProfile scopes to the correct user', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $profileA = StaffProfile::factory()->for($userA)->create();
    StaffProfile::factory()->for($userB)->create();
    $shift = Shift::factory()->create();

    ShiftAssignment::create([
        'user_id' => $userA->id,
        'shift_id' => $shift->id,
        'valid_from' => Carbon::parse('2026-01-01'),
    ]);

    ShiftAssignment::create([
        'user_id' => $userB->id,
        'shift_id' => $shift->id,
        'valid_from' => Carbon::parse('2026-01-01'),
    ]);

    $assignments = $profileA->shiftAssignments;

    expect($assignments)->toHaveCount(1)
        ->and($assignments->first()->user_id)->toBe($userA->id);
});
