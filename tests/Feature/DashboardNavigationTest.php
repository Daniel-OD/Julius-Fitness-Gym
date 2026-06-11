<?php

use App\Filament\Auth\Login;
use App\Models\User;
use Database\Seeders\ClientRoleSeeder;
use Database\Seeders\EmployeeRoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function dashboardNavUser(string $role): User
{
    if ($role === 'employee') {
        (new EmployeeRoleSeeder)->run();
    }

    if ($role === 'client') {
        (new ClientRoleSeeder)->run();
    }

    Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('super admin sees all dashboard links in navigation', function (): void {
    $user = dashboardNavUser('super_admin');

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee(__('app.navigation.dashboard_admin'))
        ->assertSee(__('app.navigation.dashboard_employee'))
        ->assertSee(__('app.navigation.dashboard_client'));
});

it('employee sees only employee dashboard link in navigation', function (): void {
    $user = dashboardNavUser('employee');

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee(__('app.navigation.dashboard_employee'))
        ->assertDontSee(__('app.navigation.dashboard_admin'))
        ->assertDontSee(__('app.navigation.dashboard_client'));
});

it('client sees only client dashboard link in navigation', function (): void {
    $user = dashboardNavUser('client');

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee(__('app.navigation.dashboard_client'))
        ->assertDontSee(__('app.navigation.dashboard_admin'))
        ->assertDontSee(__('app.navigation.dashboard_employee'));
});

it('redirects super admin to admin dashboard after login', function (): void {
    $user = dashboardNavUser('super_admin');

    $this->get('/staff/login');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect(Filament::getPanel('admin')->getUrl());
});

it('redirects employee to office dashboard after login', function (): void {
    $user = dashboardNavUser('employee');

    $this->get('/office/login');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect(Filament::getPanel('office')->getUrl());
});

it('redirects client to client dashboard after login', function (): void {
    $user = dashboardNavUser('client');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('client.dashboard'));
});

it('employee is redirected away from client dashboard', function (): void {
    $user = dashboardNavUser('employee');

    $this->actingAs($user)
        ->get(route('client.dashboard'))
        ->assertRedirect(Filament::getPanel('office')->getUrl());
});

it('redirects dashboard route to role default', function (): void {
    $user = dashboardNavUser('client');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('client.dashboard'));
});

it('accessible dashboards returns expected keys per role', function (): void {
    expect(dashboardNavUser('super_admin')->accessibleDashboards())->toBe(['admin', 'office', 'client'])
        ->and(dashboardNavUser('employee')->accessibleDashboards())->toBe(['office'])
        ->and(dashboardNavUser('client')->accessibleDashboards())->toBe(['client']);
});

it('client role seeder is idempotent', function (): void {
    (new ClientRoleSeeder)->run();
    (new ClientRoleSeeder)->run();

    expect(Role::where('name', 'client')->count())->toBe(1);
});
