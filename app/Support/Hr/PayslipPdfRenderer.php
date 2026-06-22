<?php

namespace App\Support\Hr;

use App\Models\PayrollItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

final class PayslipPdfRenderer
{
    public function render(PayrollItem $item): string
    {
        File::ensureDirectoryExists(storage_path('fonts'));

        $item->loadMissing(['user.staffProfile', 'period']);

        return Pdf::loadView('hr.payslip', [
            'item' => $item,
            'period' => $item->period,
            'user' => $item->user,
            'profile' => $item->user?->staffProfile,
        ])
            ->setPaper('a4')
            ->output();
    }
}
