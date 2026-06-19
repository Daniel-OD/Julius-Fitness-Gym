<?php

namespace App\Support\Billing;

final class PaymentMethod
{
    public static function normalize(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    public static function isOnline(?string $value): bool
    {
        return in_array(self::normalize($value), ['online', 'stripe'], true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'cash' => 'Cash',
            'online' => 'Online',
            'cheque' => 'Cheque',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
        ];
    }

    public static function channelLabel(?string $value): string
    {
        return self::options()[self::normalize($value)] ?? ucfirst((string) $value);
    }
}
