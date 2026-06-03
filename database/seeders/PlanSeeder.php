<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Abonament Lunar',
                'code' => 'PLN-30',
                'description' => 'Acces nelimitat la sală timp de 30 de zile.',
                'days' => 30,
                'amount' => 150.00,
                'status' => 'active',
            ],
            [
                'name' => 'Abonament Trimestrial',
                'code' => 'PLN-90',
                'description' => 'Acces nelimitat la sală timp de 90 de zile. Economisești 10%.',
                'days' => 90,
                'amount' => 405.00,
                'status' => 'active',
            ],
            [
                'name' => 'Abonament Anual',
                'code' => 'PLN-365',
                'description' => 'Acces nelimitat la sală timp de 365 de zile. Cel mai bun raport calitate/preț.',
                'days' => 365,
                'amount' => 1440.00,
                'status' => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['code' => $plan['code']], $plan);
        }
    }
}
