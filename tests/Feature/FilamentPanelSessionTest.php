<?php

use App\Models\User;
use App\Support\FilamentSession;
use Database\Seeders\EmployeeRoleSeeder;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('locks session to office when employee signs in from admin login', function (): void {
    (new EmployeeRoleSeeder)->run();
    $employee = User::factory()->create();
    $employee->assignRole('employee');

    expect($employee->postLoginPanelId('admin'))->toBe('office');

    FilamentSession::lockToPanel('office');

    expect(FilamentSession::authenticatedPanelId())->toBe('office');
});

it('locks session to admin when super admin signs in from admin login', function (): void {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    expect($admin->postLoginPanelId('admin'))->toBe('admin');

    FilamentSession::lockToPanel('admin');

    expect(FilamentSession::authenticatedPanelId())->toBe('admin');
});

it('locks session to office when super admin signs in from office login', function (): void {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    expect($admin->postLoginPanelId('office'))->toBe('office');
});

it('clears panel lock on logout event', function (): void {
    FilamentSession::lockToPanel('admin');

    event(new Logout('web', User::factory()->make()));

    expect(FilamentSession::authenticatedPanelId())->toBeNull();
});

it('lets a super admin move between admin and office panels without being logged out', function (): void {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->withSession([FilamentSession::AUTHENTICATED_PANEL_KEY => 'admin'])
        ->get('/office')
        ->assertOk();

    expect(auth()->check())->toBeTrue();
    expect(FilamentSession::authenticatedPanelId())->toBe('office');
});

it('still logs out an employee-only user who crosses into a panel they were not locked to', function (): void {
    (new EmployeeRoleSeeder)->run();
    $employee = User::factory()->create();
    $employee->assignRole('employee');

    $this->actingAs($employee)
        ->withSession([FilamentSession::AUTHENTICATED_PANEL_KEY => 'admin'])
        ->get('/office')
        ->assertRedirect();

    expect(auth()->check())->toBeFalse();
});
