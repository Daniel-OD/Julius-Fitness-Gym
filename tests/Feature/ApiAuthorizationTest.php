<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * User with zero permissions — simulates employee or low-privilege token.
 */
function unprivilegedUser(): User
{
    $role = Role::firstOrCreate(['name' => 'unprivileged', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ─── Every protected endpoint returns 403 for a user with no permissions ─────

it('GET /api/v1/members returns 403 without ViewAny:Member', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/members')->assertForbidden();
});

it('POST /api/v1/members returns 403 without Create:Member', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->postJson('/api/v1/members', [
        'name' => 'x', 'email' => 'x@x.com', 'contact' => '0700',
    ])->assertForbidden();
});

it('GET /api/v1/plans returns 403 without ViewAny:Plan', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/plans')->assertForbidden();
});

it('GET /api/v1/services returns 403 without ViewAny:Service', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/services')->assertForbidden();
});

it('GET /api/v1/subscriptions returns 403 without ViewAny:Subscription', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/subscriptions')->assertForbidden();
});

it('GET /api/v1/invoices returns 403 without ViewAny:Invoice', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/invoices')->assertForbidden();
});

it('GET /api/v1/settings returns 403 without View:Settings', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/settings')->assertForbidden();
});

it('PUT /api/v1/settings returns 403 without View:Settings', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->putJson('/api/v1/settings', [])->assertForbidden();
});

it('GET /api/v1/analytics/financial returns 403 without ViewAny:Invoice', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/analytics/financial')->assertForbidden();
});

it('GET /api/v1/analytics/membership returns 403 without ViewAny:Member', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/analytics/membership')->assertForbidden();
});

it('GET /api/v1/analytics/cashflow-trend returns 403 without ViewAny:Invoice', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/analytics/cashflow-trend')->assertForbidden();
});

it('GET /api/v1/analytics/expense-categories returns 403 without ViewAny:Expense', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/analytics/expense-categories')->assertForbidden();
});

it('GET /api/v1/analytics/top-plans returns 403 without ViewAny:Plan', function (): void {
    Sanctum::actingAs(unprivilegedUser());

    $this->getJson('/api/v1/analytics/top-plans')->assertForbidden();
});

// ─── Unauthenticated access is always 401 ─────────────────────────────────

it('unauthenticated request to members returns 401', function (): void {
    $this->getJson('/api/v1/members')->assertUnauthorized();
});

it('unauthenticated request to settings returns 401', function (): void {
    $this->getJson('/api/v1/settings')->assertUnauthorized();
});

it('unauthenticated request to analytics returns 401', function (): void {
    $this->getJson('/api/v1/analytics/financial')->assertUnauthorized();
});

// ─── Soft-delete restore/force require correct permissions ────────────────

it('restore returns 403 without Restore:Member permission', function (): void {
    Sanctum::actingAs(unprivilegedUser());
    $member = Member::factory()->create();
    $member->delete();

    $this->postJson("/api/v1/members/{$member->id}/restore")->assertForbidden();
});

it('force-delete returns 403 without ForceDelete:Member permission', function (): void {
    Sanctum::actingAs(unprivilegedUser());
    $member = Member::factory()->create();
    $member->delete();

    $this->deleteJson("/api/v1/members/{$member->id}/force")->assertForbidden();
});
