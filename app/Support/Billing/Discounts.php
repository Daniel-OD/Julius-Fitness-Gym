<?php

namespace App\Support\Billing;

use Illuminate\Support\Number;

final class Discounts
{
    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, string>
     */
    public static function optionsFromSettings(array $settings): array
    {
        $discounts = $settings['charges']['discounts'] ?? [];
        if (! is_array($discounts)) {
            return [];
        }

        $options = [];
        foreach ($discounts as $value) {
            $value = (string) $value;
            $options[$value] = Number::percentage((float) $value);
        }

        return $options;
    }

    public static function amount(?float $discountPercent, ?float $fee): float
    {
        $fee = (float) ($fee ?? 0);
        $discountPercent = (float) ($discountPercent ?? 0);

        if ($discountPercent <= 0) {
            return 0.0;
        }

        return round(($fee * $discountPercent) / 100, 2);
    }
}
