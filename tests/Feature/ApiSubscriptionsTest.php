<?php

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function subApiUser(): User
{
    $permissions = [
        'ViewAny:Subscription', 'View:Subscription', 'Create:Subscription', 'Update:Subscription',
        'Delete:Subscription', 'DeleteAny:Subscription', 'ForceDelete:Subscription',
        'ForceDeleteAny:Subscription', 'Restore:Subscription', 'RestoreAny:Subscription',
        'ViewAny:Member', 'ViewAny:Plan', 'ViewAny:Invoice', 'Create:Invoice',
    ];
    $role = Role::firstOrCreate(['name' => 'sub_admin', 'guard_name' => 'web']);
    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    $role->syncPermissions($permissions);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function makeActiveSubscription(): Subscription
{
    $today = CarbonImmutable::today();

    return Subscription::factory()->create([
        'start_date' => $today->subDays(5)->toDateString(),
        'end_date' => $today->addDays(25)->toDateString(),
        'status' => 'ongoing',
    ]);
}

it('GET /api/v1/subscriptions returns paginated list', function (): void {
    Sanctum::actingAs(subApiUser());
    makeActiveSubscription();

    getJson('/api/v1/subscriptions')->assertOk()->assertJsonStructure(['data', 'meta']);
});

it('POST /api/v1/subscriptions creates subscription', function (): void {
    Sanctum::actingAs(subApiUser());
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['days' => 30]);
    $today = now()->toDateString();

    postJson('/api/v1/subscriptions', [
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => $today,
    ])->assertCreated()->assertJsonPath('data.member_id', $member->id);
});

it('GET /api/v1/subscriptions/{id} returns subscription', function (): void {
    Sanctum::actingAs(subApiUser());
    $sub = makeActiveSubscription();

    getJson("/api/v1/subscriptions/{$sub->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $sub->id);
});

it('PATCH /api/v1/subscriptions/{id} updates subscription', function (): void {
    Sanctum::actingAs(subApiUser());
    $sub = makeActiveSubscription();

    // Assert update succeeds — internal_note is accepted but not in the resource payload
    patchJson("/api/v1/subscriptions/{$sub->id}", [
        'internal_note' => 'Test note',
    ])->assertOk()->assertJsonPath('data.id', $sub->id);
});

it('DELETE /api/v1/subscriptions/{id} soft-deletes subscription', function (): void {
    Sanctum::actingAs(subApiUser());
    $sub = makeActiveSubscription();

    deleteJson("/api/v1/subscriptions/{$sub->id}")->assertNoContent();

    expect(Subscription::find($sub->id))->toBeNull();
    expect(Subscription::withTrashed()->find($sub->id))->not->toBeNull();
});

it('POST /api/v1/subscriptions/{id}/restore restores subscription', function (): void {
    Sanctum::actingAs(subApiUser());
    $sub = makeActiveSubscription();
    $sub->delete();

    postJson("/api/v1/subscriptions/{$sub->id}/restore")->assertOk();

    expect(Subscription::find($sub->id))->not->toBeNull();
});

it('DELETE /api/v1/subscriptions/{id}/force permanently deletes subscription', function (): void {
    Sanctum::actingAs(subApiUser());
    $sub = makeActiveSubscription();
    $sub->delete();

    deleteJson("/api/v1/subscriptions/{$sub->id}/force")->assertNoContent();

    expect(Subscription::withTrashed()->find($sub->id))->toBeNull();
});

it('subscriptions returns 403 without permission', function (): void {
    $role = Role::firstOrCreate(['name' => 'no_perms_sub', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);
    Sanctum::actingAs($user);

    getJson('/api/v1/subscriptions')->assertForbidden();
});
