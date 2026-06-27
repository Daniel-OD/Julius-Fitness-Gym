<?php

use App\Contracts\SettingsRepository;
use App\Jobs\SendWhatsAppPaymentConfirmation;
use App\Models\Invoice;
use App\Models\InvoiceTransaction;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

function paidInvoice(): Invoice
{
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => 150, 'days' => 30, 'status' => 'active']);
    $sub = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(30)->toDateString(),
    ]);

    return Invoice::factory()->create([
        'subscription_id' => $sub->id,
        'date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'subscription_fee' => 150,
        'total_amount' => 150,
        'due_amount' => 150,
        'paid_amount' => 0,
        'status' => 'issued',
    ]);
}

it('dispatches the WhatsApp payment confirmation even when email receipts are disabled', function (): void {
    app(SettingsRepository::class)->put([
        'notifications' => [
            'email' => ['enabled' => false, 'auto_send_payment_receipt' => false],
            'whatsapp' => ['enabled' => true],
        ],
    ]);

    Bus::fake();

    $invoice = paidInvoice();

    InvoiceTransaction::create([
        'invoice_id' => $invoice->id,
        'type' => 'payment',
        'amount' => 150.0,
        'occurred_at' => now(),
        'payment_method' => 'cash',
        'note' => 'Full payment',
    ]);

    Bus::assertDispatched(SendWhatsAppPaymentConfirmation::class);
});

it('does not dispatch the WhatsApp payment confirmation when whatsapp is disabled', function (): void {
    app(SettingsRepository::class)->put([
        'notifications' => [
            'email' => ['enabled' => false, 'auto_send_payment_receipt' => false],
            'whatsapp' => ['enabled' => false],
        ],
    ]);

    Bus::fake();

    $invoice = paidInvoice();

    InvoiceTransaction::create([
        'invoice_id' => $invoice->id,
        'type' => 'payment',
        'amount' => 150.0,
        'occurred_at' => now(),
        'payment_method' => 'cash',
        'note' => 'Full payment',
    ]);

    Bus::assertNotDispatched(SendWhatsAppPaymentConfirmation::class);
});
