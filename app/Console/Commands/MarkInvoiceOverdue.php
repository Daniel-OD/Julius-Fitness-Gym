<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Support\AppConfig;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Description('Perform operations on invoices (e.g., mark as overdue)')]
#[Signature('gym:invoices {--mark-overdue : Mark invoices as overdue based on due date}')]
class MarkInvoiceOverdue extends Command
{
    public function handle(): int
    {
        if (! $this->option('mark-overdue')) {
            $this->info('No operation selected.');

            return self::SUCCESS;
        }

        $today = Carbon::today(AppConfig::timezone());

        $updatedCount = Invoice::query()
            ->whereIn('status', ['issued', 'partial'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->where('due_amount', '>', 0)
            ->update(['status' => 'overdue']);

        $this->info("{$updatedCount} invoice(s) marked as overdue.");

        return self::SUCCESS;
    }
}
