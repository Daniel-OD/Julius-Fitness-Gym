<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppPaymentConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $invoiceId) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $invoice = Invoice::with('subscription.member')->find($this->invoiceId);

        if (! $invoice) {
            return;
        }

        $member = $invoice->subscription?->member;

        if (! $member) {
            return;
        }

        $whatsApp->sendPaymentConfirmation($member, $invoice);
    }
}
