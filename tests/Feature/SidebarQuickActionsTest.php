<?php

use App\Models\User;
use Database\Seeders\EmployeeRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function dashboardQuickActionsAdmin(): User
{
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create(['must_change_password' => false]);
    $user->assignRole('super_admin');

    return $user;
}

it('shows quick actions as a sidebar group between dashboard and sales', function (): void {
    $this->actingAs(dashboardQuickActionsAdmin())
        ->get(route('filament.admin.pages.dashboard'))
        ->assertSuccessful()
        ->assertSee(__('app.navigation.quick_actions'), false)
        ->assertSee(__('app.dashboard.quick_actions.new_member'), false)
        ->assertSee(__('app.dashboard.quick_actions.manual_checkin'), false)
        ->assertSee(__('app.dashboard.quick_actions.new_lead'), false)
        ->assertSee(__('app.navigation.groups.sales'), false);
});

it('does not show quick actions on the office panel', function (): void {
    (new EmployeeRoleSeeder)->run();
    Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
    $user = User::factory()->create(['must_change_password' => false]);
    $user->assignRole('employee');

    $this->actingAs($user)
        ->get(route('filament.office.pages.dashboard'))
        ->assertSuccessful()
        ->assertDontSee(__('app.navigation.quick_actions'), false);
});
