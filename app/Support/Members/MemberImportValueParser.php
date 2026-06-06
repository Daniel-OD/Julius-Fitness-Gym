<?php

namespace App\Support\Members;

use Carbon\Carbon;
use Illuminate\Support\Str;

final class MemberImportValueParser
{
    public function parseDate(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'm/d/Y'];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, trim($value));

                if ($parsed !== false) {
                    return $parsed->toDateString();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    public function parseAmount(?string $value): ?float
    {
        if (! filled($value)) {
            return null;
        }

        $normalized = Str::of((string) $value)
            ->replace([' ', "\u{00A0}"], '')
            ->replace(',', '.')
            ->replaceMatches('/[^\d.\-]/', '')
            ->toString();

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return round((float) $normalized, 2);
    }

    public function parseDays(?string $value): ?int
    {
        if (! filled($value)) {
            return null;
        }

        $normalized = Str::of((string) $value)
            ->replace(',', '.')
            ->replaceMatches('/[^\d.]/', '')
            ->toString();

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        $days = (int) round((float) $normalized);

        return $days > 0 ? $days : null;
    }
}
