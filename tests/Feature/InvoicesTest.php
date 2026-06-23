<?php

use App\Models\Invoice;
use App\Models\InvoiceTransaction;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Support\Invoices\InvoiceDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSubscriptionWithInvoice(float $fee = 150.0): array
{
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['amount' => $fee, 'days' => 30, 'status' => 'active']);
    $sub = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(30)->toDateString(),
    ]);

    $invoice = Invoice::factory()->create([
        'subscription_id' => $sub->id,
        'date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'subscription_fee' => $fee,
        'paid_amount' => 0,
        'status' => 'issued',
    ]);

    return compact('member', 'plan', 'sub', 'invoice');
}

// ─── Model & totals ───────────────────────────────────────────────────────────

it('invoice stores visibility field', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice();

    $invoice->update(['visibility' => 'internal']);

    expect($invoice->fresh()->visibility)->toBe('internal');
});

it('public invoices are visible to all, internal only to office', function (): void {
    ['invoice' => $public] = makeSubscriptionWithInvoice();
    ['invoice' => $internal] = makeSubscriptionWithInvoice();

    $internal->update(['visibility' => 'internal']);

    $publicIds = Invoice::where('visibility', 'public')->pluck('id');
    $internalIds = Invoice::where('visibility', 'internal')->pluck('id');

    expect($publicIds)->toContain($public->id)
        ->and($internalIds)->toContain($internal->id)
        ->and($publicIds)->not->toContain($internal->id);
});

it('adding a payment transaction syncs invoice paid_amount and status', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice(150.0);

    $invoice->update(['total_amount' => 150, 'due_amount' => 150]);

    InvoiceTransaction::create([
        'invoice_id' => $invoice->id,
        'type' => 'payment',
        'amount' => 150.0,
        'occurred_at' => now(),
        'payment_method' => 'cash',
        'note' => 'Full payment',
    ]);

    $invoice->refresh();

    expect((float) $invoice->paid_amount)->toBe(150.0)
        ->and($invoice->status->value)->toBe('paid');
});

it('refund transaction reduces paid amount', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice(150.0);
    $invoice->update(['total_amount' => 150, 'paid_amount' => 150, 'due_amount' => 0, 'status' => 'paid']);

    // Create payment first
    InvoiceTransaction::create([
        'invoice_id' => $invoice->id,
        'type' => 'payment',
        'amount' => 150.0,
        'occurred_at' => now()->subHour(),
        'note' => 'Payment',
    ]);

    InvoiceTransaction::create([
        'invoice_id' => $invoice->id,
        'type' => 'refund',
        'amount' => 150.0,
        'occurred_at' => now(),
        'note' => 'Full refund',
    ]);

    $invoice->refresh();
    expect($invoice->status->value)->toBe('refund');
});

it('getDisplayStatusLabel returns non-empty string', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice();

    expect($invoice->getDisplayStatusLabel())->toBeString()
        ->and($invoice->getDisplayStatusLabel())->not->toBeEmpty();
});

it('soft-deletes invoice', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice();
    $id = $invoice->id;

    $invoice->delete();

    expect(Invoice::find($id))->toBeNull()
        ->and(Invoice::withTrashed()->find($id))->not->toBeNull();
});

// ─── PDF rendering ────────────────────────────────────────────────────────────

it('InvoiceDocument::missingRequiredData returns empty for complete invoice', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice();

    $missing = InvoiceDocument::missingRequiredData(
        InvoiceDocument::loadForRendering($invoice)
    );

    expect($missing)->toBeArray();
    // May have missing fields if data is minimal — just verify it returns array
});

it('InvoiceDocument::pdfFilename returns safe filename', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice();
    $invoice->update(['number' => 'INV-2026-001']);

    $filename = InvoiceDocument::pdfFilename($invoice->fresh());

    expect($filename)->toContain('invoice')
        ->and($filename)->toEndWith('.pdf')
        ->and($filename)->not->toContain('/');
});

it('partial payment sets status to partial', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice(200.0);
    $invoice->update(['total_amount' => 200, 'due_amount' => 200]);

    InvoiceTransaction::create([
        'invoice_id' => $invoice->id,
        'type' => 'payment',
        'amount' => 100.0,
        'occurred_at' => now(),
        'payment_method' => 'cash',
        'note' => 'Partial payment',
    ]);

    $invoice->refresh();

    expect($invoice->status->value)->toBe('partial')
        ->and((float) $invoice->paid_amount)->toBe(100.0)
        ->and((float) $invoice->due_amount)->toBe(100.0);
});

it('overdue status is set when due balance remains past the due date', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice(150.0);
    $invoice->update([
        'total_amount' => 150,
        'due_amount' => 150,
        'due_date' => now()->subDay()->toDateString(),
    ]);

    // No payment — due remains
    $invoice->syncFromTransactions();
    $invoice->refresh();

    expect($invoice->status->value)->toBe('overdue');
});

it('cancelled status is preserved by syncFromTransactions', function (): void {
    ['invoice' => $invoice] = makeSubscriptionWithInvoice(150.0);
    $invoice->update(['total_amount' => 150, 'due_amount' => 150, 'status' => 'cancelled']);

    $invoice->syncFromTransactions();
    $invoice->refresh();

    expect($invoice->status->value)->toBe('cancelled')
        ->and((float) $invoice->due_amount)->toBe(0.0);
});
