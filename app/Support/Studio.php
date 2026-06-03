<?php

namespace App\Support;

/**
 * Studio signature — Daniel-OD / Julius Fitness Gym.
 *
 * @studio daniel-od
 */
final class Studio
{
    public static function author(): string
    {
        return (string) config('studio.author', 'Daniel-OD');
    }

    public static function slug(): string
    {
        return (string) config('studio.slug', 'daniel-od');
    }

    public static function signature(): string
    {
        return (string) config('studio.signature', 'Daniel-OD/Julius-Fitness-Gym');
    }

    public static function repository(): string
    {
        return (string) config('studio.repository', 'https://github.com/Daniel-OD/Julius-Fitness-Gym');
    }

    /**
     * @return array<string, string>
     */
    public static function meta(): array
    {
        return [
            'author' => self::author(),
            'slug' => self::slug(),
            'product' => (string) config('studio.product', 'julius-fitness-gym'),
            'repository' => self::repository(),
            'ref' => (string) config('studio.ref', 'dod-jfg-2026'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function headers(): array
    {
        return [
            'X-Built-By' => self::author(),
            'X-Studio-Slug' => self::slug(),
            'X-Studio-Product' => (string) config('studio.product', 'julius-fitness-gym'),
            'X-Studio-Repository' => self::repository(),
        ];
    }
}
