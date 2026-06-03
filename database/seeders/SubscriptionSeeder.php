<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::all();
        $plans = Plan::all();

        if ($members->isEmpty() || $plans->isEmpty()) {
            return;
        }

        $today = Carbon::today();

        $entries = [
            // Andrei — abonament lunar activ
            [
                'member_email' => 'andrei.popescu@example.ro',
                'plan_code' => 'PLN-30',
                'start_date' => $today->copy()->subDays(15)->toDateString(),
                'status' => 'ongoing',
            ],
            // Maria — abonament trimestrial activ
            [
                'member_email' => 'maria.ionescu@example.ro',
                'plan_code' => 'PLN-90',
                'start_date' => $today->copy()->subDays(30)->toDateString(),
                'status' => 'ongoing',
            ],
            // Ion — abonament anual activ
            [
                'member_email' => 'ion.dumitrescu@example.ro',
                'plan_code' => 'PLN-365',
                'start_date' => $today->copy()->subDays(60)->toDateString(),
                'status' => 'ongoing',
            ],
            // Elena — abonament lunar expirat
            [
                'member_email' => 'elena.constantin@example.ro',
                'plan_code' => 'PLN-30',
                'start_date' => $today->copy()->subDays(45)->toDateString(),
                'status' => 'expired',
            ],
            // Mihai — abonament trimestrial care expiră curând
            [
                'member_email' => 'mihai.georgescu@example.ro',
                'plan_code' => 'PLN-90',
                'start_date' => $today->copy()->subDays(85)->toDateString(),
                'status' => 'expiring',
            ],
        ];

        foreach ($entries as $entry) {
            $member = $members->firstWhere('email', $entry['member_email']);
            $plan = $plans->firstWhere('code', $entry['plan_code']);

            if (! $member || ! $plan) {
                continue;
            }

            $endDate = Carbon::parse($entry['start_date'])->addDays((int) $plan->days)->toDateString();

            Subscription::firstOrCreate(
                ['member_id' => $member->id, 'start_date' => $entry['start_date']],
                [
                    'plan_id' => $plan->id,
                    'end_date' => $endDate,
                    'status' => $entry['status'],
                ]
            );
        }
    }
}
