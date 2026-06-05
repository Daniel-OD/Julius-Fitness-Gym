<?php

use App\Models\Member;
use App\Models\Plan;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * Build an API user with all permissions needed across API tests.
 *
 * Shield is configured with super_admin.define_via_gate = false — there is no
 * global gate bypass; permissions must exist and be granted explicitly.
 */
function apiUser(array $extraPermissions = []): User
{
    $permissions = array_merge([
        'ViewAny:Member', 'View:Member', 'Create:Member', 'Update:Member',
        'Delete:Member', 'DeleteAny:Member', 'ForceDelete:Member', 'ForceDeleteAny:Member',
        'Restore:Member', 'RestoreAny:Member',
        'ViewAny:Plan', 'View:Plan', 'Create:Plan', 'Update:Plan',
        'Delete:Plan', 'DeleteAny:Plan', 'ForceDelete:Plan', 'ForceDeleteAny:Plan',
        'Restore:Plan', 'RestoreAny:Plan',
        'ViewAny:Service', 'View:Service', 'Create:Service', 'Update:Service',
        'Delete:Service', 'DeleteAny:Service', 'ForceDelete:Service', 'ForceDeleteAny:Service',
        'Restore:Service', 'RestoreAny:Service',
        'ViewAny:Subscription', 'View:Subscription', 'Create:Subscription', 'Update:Subscription',
        'Delete:Subscription', 'DeleteAny:Subscription', 'ForceDelete:Subscription',
        'ForceDeleteAny:Subscription', 'Restore:Subscription', 'RestoreAny:Subscription',
        'ViewAny:Invoice', 'View:Invoice', 'Create:Invoice', 'Update:Invoice',
        'Delete:Invoice', 'DeleteAny:Invoice', 'ForceDelete:Invoice',
        'ForceDeleteAny:Invoice', 'Restore:Invoice', 'RestoreAny:Invoice',
        'ViewAny:Expense', 'View:Expense', 'Create:Expense', 'Update:Expense', 'Delete:Expense',
        'View:Settings', 'ViewAny:User',
    ], $extraPermissions);

    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    $role->syncPermissions($permissions);

    $user = User::factory()->create(['password' => bcrypt('password')]);
    $user->assignRole($role);

    return $user;
}

function noPermUser(): User
{
    $role = Role::firstOrCreate(['name' => 'no_perms', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ─── Auth ────────────────────────────────────────────────────────────────────

it('login returns sanctum token', function (): void {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()->assertJsonStructure(['token']);
});

it('login fails with wrong password', function (): void {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong',
    ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
});

it('login fails with missing fields', function (): void {
    $this->postJson('/api/v1/auth/login', [])->assertUnprocessable();
});

it('GET /api/v1/me returns authenticated user', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);
});

it('logout revokes current token', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/auth/logout')->assertNoContent();
});

it('api returns 401 without token', function (): void {
    $this->getJson('/api/v1/members')->assertUnauthorized();
});

it('api always returns JSON even without Accept header', function (): void {
    $response = $this->get('/api/v1/members');

    expect($response->headers->get('Content-Type'))->toContain('application/json');
});

// ─── Members ─────────────────────────────────────────────────────────────────

it('GET /api/v1/members returns paginated list', function (): void {
    Sanctum::actingAs(apiUser());
    Member::factory()->count(3)->create();

    $this->getJson('/api/v1/members')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

it('POST /api/v1/members creates a member', function (): void {
    Sanctum::actingAs(apiUser());

    $this->postJson('/api/v1/members', [
        'name' => 'API Member',
        'email' => 'api.member@example.ro',
        'contact' => '0700000001',
        'status' => 'active',
    ])->assertCreated()->assertJsonPath('data.name', 'API Member');

    expect(Member::where('email', 'api.member@example.ro')->exists())->toBeTrue();
});

it('POST /api/v1/members rejects without required fields', function (): void {
    Sanctum::actingAs(apiUser());

    $this->postJson('/api/v1/members', [])->assertUnprocessable()->assertJsonStructure(['errors']);
});

it('POST /api/v1/members rejects duplicate email', function (): void {
    Sanctum::actingAs(apiUser());
    $existing = Member::factory()->create();

    $this->postJson('/api/v1/members', [
        'name' => 'Dupe',
        'email' => $existing->email,
        'contact' => '0700000002',
    ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
});

it('GET /api/v1/members/{id} returns member', function (): void {
    Sanctum::actingAs(apiUser());
    $member = Member::factory()->create();

    $this->getJson("/api/v1/members/{$member->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $member->id);
});

it('GET /api/v1/members/{id} returns 404 for non-existent member', function (): void {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/members/99999')->assertNotFound();
});

it('PATCH /api/v1/members/{id} updates member', function (): void {
    Sanctum::actingAs(apiUser());
    $member = Member::factory()->create(['name' => 'Original']);

    $this->patchJson("/api/v1/members/{$member->id}", ['name' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated');
});

it('DELETE /api/v1/members/{id} soft-deletes member', function (): void {
    Sanctum::actingAs(apiUser());
    $member = Member::factory()->create();

    $this->deleteJson("/api/v1/members/{$member->id}")->assertNoContent();

    expect(Member::find($member->id))->toBeNull();
    expect(Member::withTrashed()->find($member->id))->not->toBeNull();
});

it('POST /api/v1/members/{id}/restore restores soft-deleted member', function (): void {
    Sanctum::actingAs(apiUser());
    $member = Member::factory()->create();
    $member->delete();

    $this->postJson("/api/v1/members/{$member->id}/restore")->assertOk();

    expect(Member::find($member->id))->not->toBeNull();
});

it('DELETE /api/v1/members/{id}/force permanently deletes member', function (): void {
    Sanctum::actingAs(apiUser());
    $member = Member::factory()->create();
    $member->delete();

    $this->deleteJson("/api/v1/members/{$member->id}/force")->assertNoContent();

    expect(Member::withTrashed()->find($member->id))->toBeNull();
});

it('member checkin_token cannot be set via API', function (): void {
    Sanctum::actingAs(apiUser());
    $member = Member::factory()->create();
    $original = $member->checkin_token;

    $this->patchJson("/api/v1/members/{$member->id}", [
        'checkin_token' => 'hijacked-token',
    ])->assertOk();

    expect($member->fresh()->checkin_token)->toBe($original);
});

// ─── Plans ───────────────────────────────────────────────────────────────────

it('GET /api/v1/plans returns plans list', function (): void {
    Sanctum::actingAs(apiUser());
    Plan::factory()->count(2)->create();

    $this->getJson('/api/v1/plans')->assertOk()->assertJsonStructure(['data']);
});

it('POST /api/v1/plans creates a plan', function (): void {
    Sanctum::actingAs(apiUser());
    $service = Service::factory()->create();

    $this->postJson('/api/v1/plans', [
        'code' => 'PLN-TEST-001',
        'name' => 'Test Plan',
        'service_id' => $service->id,
        'days' => 30,
        'amount' => 150.00,
        'status' => 'active',
    ])->assertCreated()->assertJsonPath('data.name', 'Test Plan');
});

it('GET /api/v1/plans/{id} returns plan', function (): void {
    Sanctum::actingAs(apiUser());
    $plan = Plan::factory()->create();

    $this->getJson("/api/v1/plans/{$plan->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $plan->id);
});

it('PATCH /api/v1/plans/{id} updates plan', function (): void {
    Sanctum::actingAs(apiUser());
    $plan = Plan::factory()->create(['name' => 'Old']);

    $this->patchJson("/api/v1/plans/{$plan->id}", ['name' => 'New'])
        ->assertOk()
        ->assertJsonPath('data.name', 'New');
});

it('DELETE /api/v1/plans/{id} soft-deletes plan', function (): void {
    Sanctum::actingAs(apiUser());
    $plan = Plan::factory()->create();

    $this->deleteJson("/api/v1/plans/{$plan->id}")->assertNoContent();

    expect(Plan::find($plan->id))->toBeNull();
    expect(Plan::withTrashed()->find($plan->id))->not->toBeNull();
});

it('POST /api/v1/plans/{id}/restore restores plan', function (): void {
    Sanctum::actingAs(apiUser());
    $plan = Plan::factory()->create();
    $plan->delete();

    $this->postJson("/api/v1/plans/{$plan->id}/restore")->assertOk();

    expect(Plan::find($plan->id))->not->toBeNull();
});

it('DELETE /api/v1/plans/{id}/force permanently deletes plan', function (): void {
    Sanctum::actingAs(apiUser());
    $plan = Plan::factory()->create();
    $plan->delete();

    $this->deleteJson("/api/v1/plans/{$plan->id}/force")->assertNoContent();

    expect(Plan::withTrashed()->find($plan->id))->toBeNull();
});

// ─── Analytics ───────────────────────────────────────────────────────────────

it('GET /api/v1/analytics/financial returns metrics', function (): void {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/analytics/financial')
        ->assertOk()
        ->assertJsonStructure(['data' => ['metrics']]);
});

it('GET /api/v1/analytics/membership returns metrics', function (): void {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/analytics/membership')
        ->assertOk()
        ->assertJsonStructure(['data' => ['metrics']]);
});

it('GET /api/v1/analytics/cashflow-trend returns trend data', function (): void {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/analytics/cashflow-trend')->assertOk()->assertJsonStructure(['data']);
});

it('GET /api/v1/analytics/expense-categories returns categories', function (): void {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/analytics/expense-categories')->assertOk();
});

it('GET /api/v1/analytics/top-plans returns top plans', function (): void {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/analytics/top-plans')->assertOk()->assertJsonStructure(['data']);
});

// ─── Settings ────────────────────────────────────────────────────────────────

it('GET /api/v1/settings returns settings for authorized user', function (): void {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/settings')->assertOk()->assertJsonStructure(['data']);
});

it('GET /api/v1/settings returns 403 without View:Settings permission', function (): void {
    Sanctum::actingAs(noPermUser());

    $this->getJson('/api/v1/settings')->assertForbidden();
});
