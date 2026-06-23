<?php

use App\Enums\CheckInStatus;
use App\Filament\Resources\CheckIns\Pages\ListCheckIns;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function checkInsAdmin(): User
{
    $permissions = [
        'ViewAny:CheckIn', 'View:CheckIn', 'Create:CheckIn', 'Update:CheckIn',
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

it('loads the admin check-ins list page with all statuses', function (): void {
    $member = Member::factory()->create();

    CheckIn::factory()->create(['member_id' => $member->id]);
    CheckIn::factory()->graceEntry()->create(['member_id' => $member->id]);
    CheckIn::factory()->blocked()->create(['member_id' => $member->id]);

    actingAs(checkInsAdmin())
        ->get(route('filament.admin.resources.check-ins.index'))
        ->assertSuccessful();
});

it('filters check-ins by status', function (): void {
    $member = Member::factory()->create();

    CheckIn::factory()->create(['member_id' => $member->id]);
    $blocked = CheckIn::factory()->blocked()->create(['member_id' => $member->id]);

    Livewire::actingAs(checkInsAdmin())
        ->test(ListCheckIns::class)
        ->set('activeTab', 'all')
        ->filterTable('status', CheckInStatus::Blocked->value)
        ->assertCanSeeTableRecords([$blocked])
        ->assertCountTableRecords(1);
});

it('exports the filtered check-ins as csv', function (): void {
    $member = Member::factory()->create();

    CheckIn::factory()->create(['member_id' => $member->id]);
    CheckIn::factory()->blocked()->create(['member_id' => $member->id]);

    Livewire::actingAs(checkInsAdmin())
        ->test(ListCheckIns::class)
        ->callTableAction('exportCsv')
        ->assertSuccessful()
        ->assertDownload();
});
