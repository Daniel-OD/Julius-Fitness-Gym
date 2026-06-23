<?php

use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function subscriptionsAdmin(): User
{
    $permissions = [
        'ViewAny:Subscription', 'View:Subscription', 'Create:Subscription', 'Update:Subscription',
        'Delete:Subscription', 'DeleteAny:Subscription',
    ];
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $role->syncPermissions($permissions);

    $user = User::factory()->create(['must_change_password' => false]);
    $user->assignRole($role);

    return $user;
}

it('loads the admin subscriptions list page', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'type' => 'official',
    ]);

    actingAs(subscriptionsAdmin())
        ->get(route('filament.admin.resources.subscriptions.index'))
        ->assertSuccessful();
});

it('loads subscriptions list when no plans exist yet', function (): void {
    Livewire::actingAs(subscriptionsAdmin())
        ->test(ListSubscriptions::class)
        ->assertSuccessful();
});

it('loads subscriptions list via livewire with tabs', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'type' => 'official',
        'status' => 'ongoing',
    ]);

    Livewire::actingAs(subscriptionsAdmin())
        ->test(ListSubscriptions::class)
        ->assertSuccessful();
});

it('loads subscriptions when member or plan was soft-deleted', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create();

    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'type' => 'official',
        'status' => 'ongoing',
    ]);

    $member->delete();
    $plan->delete();

    Livewire::actingAs(subscriptionsAdmin())
        ->test(ListSubscriptions::class)
        ->assertSuccessful();
});
