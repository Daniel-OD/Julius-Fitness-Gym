<?php

namespace App\Support\Hr;

use App\Helpers\Helpers;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

final class PayrollPeriodPdfRenderer
{
    public function render(PayrollPeriod $period): string
    {
        File::ensureDirectoryExists(storage_path('fonts'));

        $period->loadMissing(['items.user.staffProfile']);

        $items = $period->items->map(fn (PayrollItem $item): array => [
            'employee_code' => $item->user?->staffProfile?->employee_code ?? '—',
            'name' => $item->user?->name ?? '—',
            'position' => $item->user?->staffProfile?->position ?? '—',
            'base_salary' => (float) $item->base_salary,
            'present_days' => (float) $item->present_days,
            'working_days' => (int) $item->working_days,
            'gross' => (float) $item->gross,
            'deductions_total' => PayrollItem::sumAdjustments($item->deductions),
            'bonuses_total' => PayrollItem::sumAdjustments($item->bonuses),
            'net' => (float) $item->net,
            'status' => $item->status->getLabel(),
        ]);

        return Pdf::loadView('hr.payroll-period', [
            'period' => $period,
            'items' => $items,
            'gymName' => Helpers::gymName(),
            'currency' => Helpers::getCurrencyCode(),
            'generatedAt' => now(),
            'totalGross' => $items->sum('gross'),
            'totalNet' => $items->sum('net'),
            'staffCount' => $items->count(),
        ])
            ->setPaper('a4', 'landscape')
            ->output();
    }
}
