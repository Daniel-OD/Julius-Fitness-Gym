<?php

use App\Models\User;
use Database\Seeders\EmployeeRoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function dashboardNavUser(string $role): User
{
    if ($role === 'employee') {
        (new EmployeeRoleSeeder)->run();
    }

    Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('super admin sees admin and employee dashboard links in navigation', function (): void {
    $user = dashboardNavUser('super_admin');

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee(__('app.navigation.dashboard_admin'))
        ->assertSee(__('app.navigation.dashboard_employee'));
});

it('employee sees only employee dashboard link in navigation', function (): void {
    $user = dashboardNavUser('employee');

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee(__('app.navigation.dashboard_employee'))
        ->assertDontSee(__('app.navigation.dashboard_admin'));
});

it('redirects super admin to admin dashboard after login', function (): void {
    $user = dashboardNavUser('super_admin');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(Filament::getPanel('admin')->getUrl());
});

it('redirects employee to office dashboard after login', function (): void {
    $user = dashboardNavUser('employee');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(Filament::getPanel('office')->getUrl());
});

it('redirects dashboard route to role default', function (): void {
    $user = dashboardNavUser('employee');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(Filament::getPanel('office')->getUrl());
});

it('accessible dashboards returns expected keys per role', function (): void {
    expect(dashboardNavUser('super_admin')->accessibleDashboards())->toBe(['admin', 'office'])
        ->and(dashboardNavUser('employee')->accessibleDashboards())->toBe(['office']);
});
