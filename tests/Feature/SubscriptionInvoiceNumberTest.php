<?php

use App\Filament\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Helpers\Helpers;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;

it('generates the next invoice number from settings and database', function (): void {
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create(['plan_id' => $plan->id]);

    Invoice::factory()->create([
        'subscription_id' => $subscription->id,
        'number' => 'GY-3',
        'date' => now()->toDateString(),
    ]);

    expect(Helpers::generateLastNumber('invoice', Invoice::class, now()->toDateString()))
        ->toBe('GY-4');
});

it('subscription renew falls back to generated invoice number when form omits it', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => 160, 'days' => 30, 'status' => 'active']);
    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => now()->subDays(31)->toDateString(),
        'end_date' => now()->subDay()->toDateString(),
        'status' => 'expired',
    ]);

    SubscriptionForm::handleRenew($subscription, [
        'plan_id' => $plan->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(30)->toDateString(),
        'invoice_date' => now()->toDateString(),
        'invoice_due_date' => now()->toDateString(),
        'payment_method' => 'cash',
        'paid_amount' => 0,
    ]);

    $invoice = Invoice::query()->where('subscription_id', '!=', $subscription->id)->latest('id')->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->number)->toMatch('/^GY-\d+$/');
});

it('handleRenew creates new subscription and invoice via service and marks old as renewed', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => 200, 'days' => 30, 'status' => 'active']);
    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => now()->subDays(31)->toDateString(),
        'end_date' => now()->subDay()->toDateString(),
        'status' => 'expired',
    ]);

    SubscriptionForm::handleRenew($subscription, [
        'plan_id' => $plan->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(30)->toDateString(),
        'invoice_date' => now()->toDateString(),
        'invoice_due_date' => now()->addDays(7)->toDateString(),
        'invoice_number' => 'GY-99',
        'payment_method' => 'cash',
        'paid_amount' => 200,
        'discount' => null,
        'discount_amount' => null,
        'discount_note' => null,
    ]);

    $newSub = Subscription::query()
        ->where('member_id', $member->id)
        ->where('id', '!=', $subscription->id)
        ->latest('id')
        ->first();

    expect($newSub)->not->toBeNull()
        ->and($newSub->plan_id)->toBe($plan->id)
        ->and($newSub->renewed_from_subscription_id)->toBe($subscription->id);

    $invoice = $newSub->invoices()->latest('id')->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->number)->toBe('GY-99')
        ->and((float) $invoice->subscription_fee)->toBe(200.0)
        ->and((float) $invoice->paid_amount)->toBe(200.0)
        ->and($invoice->due_date->toDateString())->toBe(now()->addDays(7)->toDateString());

    expect($subscription->fresh()->status->value)->toBe('renewed');
});

it('handleRenew caps paid_amount to plan total when overpaid', function (): void {
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => 150, 'days' => 30, 'status' => 'active']);
    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => now()->subDays(31)->toDateString(),
        'end_date' => now()->subDay()->toDateString(),
        'status' => 'expired',
    ]);

    SubscriptionForm::handleRenew($subscription, [
        'plan_id' => $plan->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(30)->toDateString(),
        'invoice_date' => now()->toDateString(),
        'invoice_due_date' => now()->toDateString(),
        'payment_method' => 'cash',
        'paid_amount' => 9999,
    ]);

    $newSub = Subscription::query()
        ->where('member_id', $member->id)
        ->where('id', '!=', $subscription->id)
        ->latest('id')
        ->first();

    $invoice = $newSub->invoices()->first();

    expect((float) $invoice->paid_amount)->toBeLessThanOrEqual((float) $invoice->total_amount);
});
