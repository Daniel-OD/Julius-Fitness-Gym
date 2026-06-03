<?php

use App\Models\Member;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function apiUser(): User
{
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create(['password' => bcrypt('password')]);
    $user->assignRole($role);

    return $user;
}

// ─── Auth ─────────────────────────────────────────────────────────────────────

it('login returns sanctum token', function (): void {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonStructure(['token']);
});

it('login fails with wrong password', function (): void {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong',
    ])->assertStatus(401);
});

it('logout revokes token', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/auth/logout')->assertNoContent();
});

// ─── Unauthenticated guard ────────────────────────────────────────────────────

it('api returns 401 without token', function (): void {
    $this->getJson('/api/v1/members')->assertStatus(401);
});

// ─── Members API ─────────────────────────────────────────────────────────────

it('GET /api/v1/members returns paginated list', function (): void {
    Sanctum::actingAs(apiUser());
    Member::factory()->count(3)->create();

    $this->getJson('/api/v1/members')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

it('POST /api/v1/members creates member', function (): void {
    Sanctum::actingAs(apiUser());

    $this->postJson('/api/v1/members', [
        'name' => 'API Member',
        'email' => 'api.member@example.ro',
        'contact' => '0700 000 001',
        'status' => 'active',
    ])->assertCreated()
        ->assertJsonPath('data.name', 'API Member');

    expect(Member::where('email', 'api.member@example.ro')->exists())->toBeTrue();
});

it('POST /api/v1/members validates required fields', function (): void {
    Sanctum::actingAs(apiUser());

    $this->postJson('/api/v1/members', [])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});

it('GET /api/v1/members/{id} returns member', function (): void {
    Sanctum::actingAs(apiUser());
    $member = Member::factory()->create();

    $this->getJson("/api/v1/members/{$member->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $member->id);
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
});

// ─── Plans API ────────────────────────────────────────────────────────────────

it('GET /api/v1/plans returns plans', function (): void {
    Sanctum::actingAs(apiUser());
    Plan::factory()->count(2)->create();

    $this->getJson('/api/v1/plans')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

// ─── Analytics API ────────────────────────────────────────────────────────────

it('GET /api/v1/analytics/financial returns financial metrics', function (): void {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/analytics/financial')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('GET /api/v1/analytics/membership returns membership metrics', function (): void {
    Sanctum::actingAs(apiUser());

    $this->getJson('/api/v1/analytics/membership')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

// ─── ForceJsonResponse middleware ────────────────────────────────────────────

it('api always returns JSON even without Accept header', function (): void {
    $response = $this->get('/api/v1/members');

    expect($response->headers->get('Content-Type'))->toContain('application/json');
});
