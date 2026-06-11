<?php

use App\Filament\Resources\CheckIns\CheckInResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Members\MemberResource;
use App\Models\User;
use Database\Seeders\EmployeeRoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function employeeUser(): User
{
    (new EmployeeRoleSeeder)->run();
    $user = User::factory()->create();
    $user->assignRole('employee');

    return $user;
}

function roleUser(string $role): User
{
    Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// ─── Access control ───────────────────────────────────────────────────────────

it('employee can access the office panel', function (): void {
    $panel = Filament::getPanel('office');

    expect(employeeUser()->canAccessPanel($panel))->toBeTrue();
});

it('employee cannot access the admin panel', function (): void {
    $panel = Filament::getPanel('admin');

    expect(employeeUser()->canAccessPanel($panel))->toBeFalse();
});

it('owner can access both panels', function (): void {
    $owner = roleUser('owner');

    expect($owner->canAccessPanel(Filament::getPanel('office')))->toBeTrue()
        ->and($owner->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
});

it('a user without roles cannot access the admin panel', function (): void {
    $user = User::factory()->create();

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

it('a user without roles cannot access the office panel', function (): void {
    $user = User::factory()->create();

    expect($user->canAccessPanel(Filament::getPanel('office')))->toBeFalse();
});

it('super admin can access both panels', function (): void {
    $user = roleUser('super_admin');

    expect($user->canAccessPanel(Filament::getPanel('office')))->toBeTrue()
        ->and($user->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
});

it('redirects employees to office after login regardless of login page', function (): void {
    $employee = employeeUser();

    expect($employee->postLoginPanelId('admin'))->toBe('office')
        ->and($employee->postLoginPanelId('office'))->toBe('office');
});

it('redirects super admin to admin when signing in from admin', function (): void {
    $admin = roleUser('super_admin');

    expect($admin->postLoginPanelId('admin'))->toBe('admin');
});

it('redirects super admin to office when signing in from office', function (): void {
    $admin = roleUser('super_admin');

    expect($admin->postLoginPanelId('office'))->toBe('office');
});

// ─── Office panel surface ─────────────────────────────────────────────────────

it('office panel exposes only the check-in resource', function (): void {
    $resources = Filament::getPanel('office')->getResources();

    expect($resources)->toContain(CheckInResource::class)
        ->and($resources)->not->toContain(MemberResource::class)
        ->and($resources)->not->toContain(InvoiceResource::class);
});

it('office panel does not register the shield roles resource', function (): void {
    $resources = Filament::getPanel('office')->getResources();

    $hasRoleResource = collect($resources)->contains(
        fn (string $resource): bool => str_contains($resource, 'Shield') || str_contains($resource, 'Role')
    );

    expect($hasRoleResource)->toBeFalse();
});

// ─── Employee role seeder ─────────────────────────────────────────────────────

it('employee role seeder grants check-in permissions only', function (): void {
    (new EmployeeRoleSeeder)->run();

    $role = Role::findByName('employee', 'web');
    $permissions = $role->permissions->pluck('name');

    expect($permissions)->toContain('ViewAny:CheckIn')
        ->and($permissions)->toContain('Create:CheckIn')
        ->and($permissions)->not->toContain('ViewAny:Member')
        ->and($permissions)->not->toContain('ViewAny:Invoice');
});

it('employee role seeder is idempotent', function (): void {
    (new EmployeeRoleSeeder)->run();
    (new EmployeeRoleSeeder)->run();

    expect(Role::where('name', 'employee')->count())->toBe(1);
});
