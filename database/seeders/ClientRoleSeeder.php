<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Client portal role for Breeze-authenticated members.
 */
class ClientRoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
    }
}
