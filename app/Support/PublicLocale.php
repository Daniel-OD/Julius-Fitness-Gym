<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class PublicLocale
{
    public const string SESSION_KEY = 'public.locale';

    /**
     * @var array<string, string>
     */
    private const array FLAGS = [
        'ro' => '🇷🇴',
        'en' => '🇬🇧',
        'it' => '🇮🇹',
        'hu' => '🇭🇺',
    ];

    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        /** @var list<string> $locales */
        $locales = config('app.public_locales', ['ro', 'en', 'it', 'hu']);

        return $locales;
    }

    public static function shouldApply(Request $request): bool
    {
        return $request->routeIs('home', 'public.locale');
    }

    public static function apply(?Request $request = null): string
    {
        $request ??= request();
        $supportedLocales = self::supported();
        $fallbackLocale = AppConfig::string('app.public_locale', 'ro');

        $queryLocale = $request->query('locale');
        $queryLocale = is_string($queryLocale) ? trim($queryLocale) : null;

        if ($queryLocale !== null && in_array($queryLocale, $supportedLocales, true) && $request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, $queryLocale);
        }

        $sessionLocale = $request->hasSession()
            ? $request->session()->get(self::SESSION_KEY)
            : null;
        $sessionLocale = is_string($sessionLocale) ? trim($sessionLocale) : null;

        $locale = $sessionLocale ?: $fallbackLocale;

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = in_array($fallbackLocale, $supportedLocales, true)
                ? $fallbackLocale
                : Data::string($supportedLocales[0] ?? 'ro', 'ro');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $locale;
    }

    public static function flag(string $locale): string
    {
        return self::FLAGS[$locale] ?? '🏳️';
    }

    /**
     * @return list<array{code: string, flag: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::supported())
            ->map(fn (string $code): array => [
                'code' => $code,
                'flag' => self::flag($code),
                'label' => (string) __("public.locales.{$code}"),
            ])
            ->values()
            ->all();
    }
}
