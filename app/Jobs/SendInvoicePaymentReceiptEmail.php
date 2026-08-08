<?php

namespace App\Jobs;

use App\Services\Email\InvoiceEmailService;
use App\Support\Invoices\InvoiceDocumentNotRenderable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Send the "payment received" receipt email (queued).
 */
class SendInvoicePaymentReceiptEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $invoiceId,
        public readonly int $invoiceTransactionId,
        public readonly string $toEmail,
        public readonly ?string $note = null,
        public readonly ?int $actorId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(InvoiceEmailService $service): void
    {
        try {
            $service->sendPaymentReceiptEmail(
                invoiceId: $this->invoiceId,
                transactionId: $this->invoiceTransactionId,
                toEmail: $this->toEmail,
                note: $this->note,
            );
        } catch (InvoiceDocumentNotRenderable $exception) {
            Log::warning('Skipping payment receipt email: missing required invoice data.', [
                'invoice_id' => $this->invoiceId,
                'invoice_transaction_id' => $this->invoiceTransactionId,
                'missing' => $exception->viewData['missing'],
            ]);
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to send invoice payment receipt email after all retries.', [
            'invoice_id' => $this->invoiceId,
            'invoice_transaction_id' => $this->invoiceTransactionId,
            'to_email' => $this->toEmail,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
