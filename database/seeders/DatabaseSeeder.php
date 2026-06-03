<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** @studio Daniel-OD */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            MemberSeeder::class,
            SubscriptionSeeder::class,
        ]);
    }
}
