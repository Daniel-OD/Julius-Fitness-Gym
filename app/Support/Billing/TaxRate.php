<?php

namespace App\Support\Billing;

final class TaxRate
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public static function fromSettings(array $settings): float
    {
        $taxRate = $settings['charges']['taxes'] ?? 0.0;

        return is_numeric($taxRate) ? (float) $taxRate : 0.0;
    }
}
