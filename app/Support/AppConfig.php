<?php

namespace App\Support;

final class AppConfig
{
    public static function string(string $key, string $default = ''): string
    {
        $value = config($key);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function timezone(): string
    {
        return self::string('app.timezone', 'UTC');
    }
}
