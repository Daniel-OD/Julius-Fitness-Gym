<?php

use App\Enums\Status;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Plan;
use App\Services\Members\MemberOnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function onboardingPlan(float $amount = 100.0, int $days = 30): Plan
{
    return Plan::factory()->create(['amount' => $amount, 'days' => $days, 'status' => 'active']);
}

function onboardingEnquiry(array $overrides = []): Enquiry
{
    return Enquiry::factory()->create(array_merge([
        'status' => 'lead',
        'name' => 'Test Lead',
        'email' => 'lead@example.com',
        'contact' => '0712000001',
        'dob' => '1990-01-01',
        'gender' => 'male',
        'address' => '123 Main St',
        'country' => 'Romania',
        'state' => null,
        'city' => 'Cluj-Napoca',
        'pincode' => '400001',
    ], $overrides));
}

function onboardingData(Plan $plan, array $overrides = []): array
{
    $today = now()->toDateString();

    return array_merge([
        'name'              => 'Test Lead',
        'email'             => 'lead@example.com',
        'contact'           => '0712000001',
        'dob'               => '1990-01-01',
        'gender'            => 'male',
        'address'           => '123 Main St',
        'country'           => 'Romania',
        'state'             => null,
        'city'              => 'Cluj-Napoca',
        'pincode'           => '400001',
        'source'            => 'promotions',
        'goal'              => 'fitness',
        'plan_id'           => $plan->id,
        'start_date'        => $today,
        'end_date'          => now()->addDays($plan->days)->toDateString(),
        'invoice_date'      => $today,
        'invoice_due_date'  => $today,
        'invoice_number'    => 'GY-TEST-1',
        'payment_method'    => 'cash',
        'paid_amount'       => $plan->amount,
        'discount'          => 0,
        'discount_amount'   => 0,
        'discount_note'     => null,
    ], $overrides);
}

it('creates a member from enquiry data', function (): void {
    $plan = onboardingPlan();
    $enquiry = onboardingEnquiry();

    $member = app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan),
    );

    expect($member)->toBeInstanceOf(Member::class)
        ->and($member->name)->toBe('Test Lead')
        ->and($member->email)->toBe('lead@example.com')
        ->and($member->contact)->toBe('0712000001')
        ->and($member->country)->toBe('Romania');
});

it('creates subscription linked to the new member', function (): void {
    $plan = onboardingPlan(amount: 200, days: 30);
    $enquiry = onboardingEnquiry();

    $member = app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan),
    );

    expect($member->subscriptions()->count())->toBe(1);

    $subscription = $member->subscriptions()->first();

    expect($subscription->plan_id)->toBe($plan->id)
        ->and($subscription->start_date->toDateString())->toBe(now()->toDateString())
        ->and($subscription->status->value)->toBe('ongoing');
});

it('creates invoice with auto-calculated totals via model boot', function (): void {
    $plan = onboardingPlan(amount: 100, days: 30);
    $enquiry = onboardingEnquiry();

    $member = app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan, ['paid_amount' => 100.0, 'invoice_number' => 'GY-INV-1']),
    );

    $invoice = $member->subscriptions()->first()->invoices()->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->number)->toBe('GY-INV-1')
        ->and((float) $invoice->subscription_fee)->toBe(100.0)
        ->and((float) $invoice->total_amount)->toBeGreaterThanOrEqual(100.0);
});

it('marks enquiry status as member after conversion', function (): void {
    $plan = onboardingPlan();
    $enquiry = onboardingEnquiry();

    app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan),
    );

    expect($enquiry->fresh()->status->value)->toBe('member');
});

it('syncs member status to active for a current subscription', function (): void {
    $plan = onboardingPlan();
    $enquiry = onboardingEnquiry();

    $member = app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan),
    );

    expect($member->fresh()->status)->toBe(Status::Active);
});

it('sets subscription status to upcoming for a future start date', function (): void {
    $plan = onboardingPlan(days: 30);
    $enquiry = onboardingEnquiry();
    $futureStart = now()->addDays(5)->toDateString();

    $member = app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan, [
            'start_date' => $futureStart,
            'end_date'   => now()->addDays(35)->toDateString(),
        ]),
    );

    $subscription = $member->subscriptions()->first();

    expect($subscription->status->value)->toBe('upcoming');
});

it('sets paid_amount to zero for online payment method', function (): void {
    $plan = onboardingPlan(amount: 150);
    $enquiry = onboardingEnquiry();

    $member = app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan, [
            'payment_method' => 'online',
            'paid_amount'    => 150.0,
        ]),
    );

    $invoice = $member->subscriptions()->first()->invoices()->first();

    expect((float) $invoice->paid_amount)->toBe(0.0);
});

it('auto-generates invoice number when none provided', function (): void {
    $plan = onboardingPlan();
    $enquiry = onboardingEnquiry();

    $member = app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan, ['invoice_number' => null]),
    );

    $invoice = $member->subscriptions()->first()->invoices()->first();

    expect($invoice->number)->not->toBeNull()
        ->and($invoice->number)->toMatch('/^GY-\d+$/');
});

it('creates invoice transaction automatically for cash payments via observer', function (): void {
    $plan = onboardingPlan(amount: 200);
    $enquiry = onboardingEnquiry();

    $member = app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan, ['paid_amount' => 200.0]),
    );

    $invoice = Invoice::query()
        ->whereHas('subscription', fn ($q) => $q->where('member_id', $member->id))
        ->first();

    expect($invoice->transactions()->count())->toBe(1);
});

// ─── create() — direct onboarding without enquiry ───────────────────────────

it('create() builds a member without requiring an enquiry', function (): void {
    $plan = onboardingPlan(amount: 120, days: 30);

    $member = app(MemberOnboardingService::class)->create(
        onboardingData($plan, ['email' => 'direct@example.com']),
    );

    expect($member)->toBeInstanceOf(Member::class)
        ->and($member->email)->toBe('direct@example.com')
        ->and($member->subscriptions()->count())->toBe(1)
        ->and($member->subscriptions()->first()->invoices()->count())->toBe(1);
});

it('create() activates member status via SubscriptionObserver', function (): void {
    $plan = onboardingPlan();

    $member = app(MemberOnboardingService::class)->create(
        onboardingData($plan, ['email' => 'direct2@example.com']),
    );

    expect($member->fresh()->status)->toBe(Status::Active);
});

it('createFromEnquiry() still marks enquiry as member after refactor', function (): void {
    $plan = onboardingPlan();
    $enquiry = onboardingEnquiry(['email' => 'enquiry-refactor@example.com']);

    app(MemberOnboardingService::class)->createFromEnquiry(
        $enquiry,
        onboardingData($plan, ['email' => 'enquiry-refactor@example.com']),
    );

    expect($enquiry->fresh()->status->value)->toBe('member');
});
