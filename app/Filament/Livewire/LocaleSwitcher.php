<?php

namespace App\Filament\Livewire;

use App\Contracts\SettingsRepository;
use App\Support\AppConfig;
use App\Support\AppLocale;
use App\Support\Data;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LocaleSwitcher extends Component
{
    public string $locale = 'en';

    /**
     * @var array<string, string>
     */
    private const array LOCALE_FLAGS = [
        'en' => '🇬🇧',
        'ro' => '🇷🇴',
    ];

    public function mount(): void
    {
        $this->locale = $this->resolveLocaleFromSettings();
    }

    private function resolveLocaleFromSettings(): string
    {
        $fallback = AppConfig::string('app.fallback_locale', 'en');
        $supported = AppConfig::supportedLocales();

        try {
            $settings = app(SettingsRepository::class)->get();
            $candidate = data_get($settings, 'general.locale');
            $locale = is_string($candidate) ? trim($candidate) : '';
        } catch (\Throwable) {
            $locale = '';
        }

        if ($locale === '' || ! in_array($locale, $supported, true)) {
            $locale = AppConfig::string('app.locale', $fallback);
        }

        if (! in_array($locale, $supported, true)) {
            return in_array($fallback, $supported, true) ? $fallback : Data::string($supported[0] ?? 'en', 'en');
        }

        return $locale;
    }

    /**
     * @return array<string, array{label: string, flag: string}>
     */
    public function getOptionsProperty(): array
    {
        $options = [];

        foreach (AppConfig::supportedLocales() as $locale) {
            $options[$locale] = [
                'label' => (string) __("app.locales.{$locale}"),
                'flag' => self::LOCALE_FLAGS[$locale] ?? '🏳️',
            ];
        }

        return $options;
    }

    public function getCurrentFlagProperty(): string
    {
        return self::LOCALE_FLAGS[$this->locale] ?? '🏳️';
    }

    public function setLocale(string $locale): mixed
    {
        $options = $this->getOptionsProperty();

        if (! array_key_exists($locale, $options)) {
            return null;
        }

        /** @var SettingsRepository $repository */
        $repository = app(SettingsRepository::class);

        $settings = $repository->get();
        data_set($settings, 'general.locale', $locale);
        /** @var array<string, mixed> $settings */
        $repository->put($settings);

        $this->locale = $locale;

        AppLocale::apply();

        Notification::make()
            ->title(__('app.notifications.language_updated'))
            ->success()
            ->send();

        $referer = request()->headers->get('referer');
        $redirectTo = is_string($referer) && $referer !== '' ? $referer : url('/');

        return redirect()->to($redirectTo);
    }

    public function render(): View
    {
        return view('filament.components.locale-switcher');
    }
}
