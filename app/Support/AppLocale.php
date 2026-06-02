<?php

namespace App\Support;

use App\Contracts\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

final class AppLocale
{
    /**
     * Resolve and apply the application locale for the current request.
     */
    public static function apply(?Request $request = null): string
    {
        $request ??= request();
        $supportedLocales = AppConfig::supportedLocales();
        $fallbackLocale = AppConfig::string('app.fallback_locale', 'en');

        $queryLocale = $request->query('locale');
        $queryLocale = is_string($queryLocale) ? trim($queryLocale) : null;

        $settingsLocale = null;
        try {
            $settings = app(SettingsRepository::class)->get();
            $candidate = data_get($settings, 'general.locale');
            $settingsLocale = is_string($candidate) ? trim($candidate) : null;
        } catch (\Throwable) {
            $settingsLocale = null;
        }

        $headerLocale = $request->getPreferredLanguage($supportedLocales);
        $headerLocale = is_string($headerLocale) ? trim($headerLocale) : null;

        $locale = $queryLocale ?: ($settingsLocale ?: ($headerLocale ?: AppConfig::string('app.locale', 'en')));

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = in_array($fallbackLocale, $supportedLocales, true)
                ? $fallbackLocale
                : Data::string($supportedLocales[0] ?? 'en', 'en');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $locale;
    }
}
