<?php

use App\Support\Billing\InvoiceCalculator;

it('rounds fractional amounts to 2 decimals instead of whole currency units', function (): void {
    $summary = InvoiceCalculator::summary(
        fee: 49.99,
        taxRatePercent: 0.0,
        discountAmount: 0.0,
        paidAmount: 0.0,
    );

    expect($summary['fee'])->toBe(49.99)
        ->and($summary['total'])->toBe(49.99)
        ->and($summary['due'])->toBe(49.99);
});

it('rounds tax and totals to 2 decimals', function (): void {
    $summary = InvoiceCalculator::summary(
        fee: 100.0,
        taxRatePercent: 19.5,
        discountAmount: 10.555,
        paidAmount: 50.005,
    );

    expect($summary['tax'])->toBe(19.5)
        ->and($summary['discount_amount'])->toBe(10.56)
        ->and($summary['total'])->toBe(108.94)
        ->and($summary['paid'])->toBe(50.01)
        ->and($summary['due'])->toBe(58.93);
});
