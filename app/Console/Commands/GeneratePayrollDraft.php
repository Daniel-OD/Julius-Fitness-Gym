<?php

namespace App\Console\Commands;

use App\Services\Hr\PayrollService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Generate payroll draft for the previous month')]
#[Signature('gym:payroll {--generate-previous : Generate draft payroll for the previous month}')]
class GeneratePayrollDraft extends Command
{
    public function handle(PayrollService $payroll): int
    {
        if (! $this->option('generate-previous')) {
            $this->info('No operation selected. Use --generate-previous.');

            return self::SUCCESS;
        }

        $period = $payroll->generatePreviousMonthDraft();

        if ($period === null) {
            $this->warn('Payroll period could not be generated.');

            return self::FAILURE;
        }

        $this->info("Payroll draft generated for {$period->label()} ({$period->items()->count()} items).");

        return self::SUCCESS;
    }
}
