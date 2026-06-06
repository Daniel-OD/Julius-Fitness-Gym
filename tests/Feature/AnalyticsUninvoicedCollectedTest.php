<?php

use App\Models\Invoice;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Analytics\AnalyticsService;
use App\Support\Analytics\AnalyticsDateRange;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-15', config('app.timezone')));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('includes prorated subscriptions without invoices in collected totals', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => 250, 'days' => 30, 'status' => 'active']);

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => '2026-06-10',
        'end_date' => '2026-07-10',
        'status' => 'ongoing',
    ]);

    $range = new AnalyticsDateRange(
        CarbonImmutable::parse('2026-06-01', config('app.timezone'))->startOfDay(),
        CarbonImmutable::parse('2026-06-30', config('app.timezone'))->endOfDay(),
    );

    $metrics = app(AnalyticsService::class)->financialMetrics($range);

    expect($metrics['collected_from_uninvoiced'])->toBe(169.35)
        ->and($metrics['collected'])->toBe(169.35)
        ->and($metrics['uninvoiced_subscriptions_count'])->toBe(1);
});

it('includes uninvoiced subscriptions that started before the selected period', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => 300, 'days' => 60, 'status' => 'active']);

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => '2026-05-01',
        'end_date' => '2026-06-30',
        'status' => 'ongoing',
    ]);

    $range = new AnalyticsDateRange(
        CarbonImmutable::parse('2026-06-01', config('app.timezone'))->startOfDay(),
        CarbonImmutable::parse('2026-06-30', config('app.timezone'))->endOfDay(),
    );

    $metrics = app(AnalyticsService::class)->financialMetrics($range);

    expect($metrics['collected_from_uninvoiced'])->toBe(147.54)
        ->and($metrics['uninvoiced_subscriptions_count'])->toBe(1);
});

it('does not double count subscriptions that have invoice payments', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => 300, 'days' => 30, 'status' => 'active']);

    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => '2026-06-05',
        'end_date' => '2026-07-05',
        'status' => 'ongoing',
    ]);

    Invoice::factory()->create([
        'subscription_id' => $subscription->id,
        'date' => '2026-06-05',
        'subscription_fee' => 300,
        'discount' => 0,
        'discount_amount' => 0,
        'paid_amount' => 300,
        'status' => 'paid',
    ]);

    $range = new AnalyticsDateRange(
        CarbonImmutable::parse('2026-06-01', config('app.timezone'))->startOfDay(),
        CarbonImmutable::parse('2026-06-30', config('app.timezone'))->endOfDay(),
    );

    $metrics = app(AnalyticsService::class)->financialMetrics($range);

    expect($metrics['collected_from_invoices'])->toBe(300.0)
        ->and($metrics['collected_from_uninvoiced'])->toBe(0.0)
        ->and($metrics['collected'])->toBe(300.0);
});

it('combines invoice payments and uninvoiced subscription revenue', function (): void {
    $memberWithInvoice = Member::factory()->create();
    $memberWithoutInvoice = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => 200, 'days' => 30, 'status' => 'active']);

    $invoicedSubscription = Subscription::factory()->create([
        'member_id' => $memberWithInvoice->id,
        'plan_id' => $plan->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-07-01',
        'status' => 'ongoing',
    ]);

    Invoice::factory()->create([
        'subscription_id' => $invoicedSubscription->id,
        'date' => '2026-06-01',
        'subscription_fee' => 200,
        'discount' => 0,
        'discount_amount' => 0,
        'paid_amount' => 200,
        'status' => 'paid',
    ]);

    Subscription::factory()->create([
        'member_id' => $memberWithoutInvoice->id,
        'plan_id' => $plan->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-07-01',
        'status' => 'ongoing',
    ]);

    $range = new AnalyticsDateRange(
        CarbonImmutable::parse('2026-06-01', config('app.timezone'))->startOfDay(),
        CarbonImmutable::parse('2026-06-30', config('app.timezone'))->endOfDay(),
    );

    $metrics = app(AnalyticsService::class)->financialMetrics($range);

    expect($metrics['collected_from_invoices'])->toBe(200.0)
        ->and($metrics['collected_from_uninvoiced'])->toBe(193.55)
        ->and($metrics['collected'])->toBe(393.55);
});
