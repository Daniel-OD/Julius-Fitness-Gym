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
