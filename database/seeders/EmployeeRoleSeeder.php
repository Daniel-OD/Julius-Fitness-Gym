<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Front-desk "employee" role.
 *
 * Employees may only operate the /office panel: view check-ins and record
 * manual check-in/check-out. They have no access to /admin, financials, or
 * any management resource. Permissions are created if missing so this works
 * before or after `php artisan shield:generate`.
 */
class EmployeeRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'ViewAny:CheckIn',
            'View:CheckIn',
            'Create:CheckIn',
            'Update:CheckIn',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
    }
}
