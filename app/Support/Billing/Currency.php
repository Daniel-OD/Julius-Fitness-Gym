<?php

namespace App\Support\Billing;

use Illuminate\Support\Number;
use NumberFormatter;

final class Currency
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public static function codeFromSettings(array $settings, string $defaultCode = 'USD'): string
    {
        $currency = $settings['general']['currency'] ?? null;

        return filled($currency) ? (string) $currency : $defaultCode;
    }

    public static function format(?float $value, string $currencyCode): string
    {
        return Number::currency($value ?? 0, $currencyCode, null, 0);
    }

    public static function symbol(string $currencyCode): string
    {
        $formatter = new NumberFormatter('en'."@currency={$currencyCode}", NumberFormatter::CURRENCY);

        return $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL) ?: '';
    }
}
