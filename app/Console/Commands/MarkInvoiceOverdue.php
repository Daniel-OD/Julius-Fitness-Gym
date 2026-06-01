<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:mark-invoice-overdue')]
#[Description('Command description')]
class MarkInvoiceOverdue extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
