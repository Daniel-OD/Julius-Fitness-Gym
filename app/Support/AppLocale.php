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

        // A locale the visitor already picked (e.g. via the public site's language
        // switcher) takes priority over the site-wide admin default and the browser's
        // Accept-Language header, so the choice stays consistent across the app.
        $sessionLocale = $request->hasSession()
            ? $request->session()->get(PublicLocale::SESSION_KEY)
            : null;
        $sessionLocale = is_string($sessionLocale) && in_array($sessionLocale, $supportedLocales, true)
            ? $sessionLocale
            : null;

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

        $locale = $queryLocale ?: ($sessionLocale ?: ($settingsLocale ?: ($headerLocale ?: AppConfig::string('app.public_locale', 'ro'))));

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
