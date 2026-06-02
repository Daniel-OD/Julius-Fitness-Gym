<?php

namespace App\Providers\Filament;

use App\Filament\Office\Pages\Dashboard;
use App\Http\Middleware\SetAppLocale;
use App\Support\AppLocale;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Front-desk Filament panel (/office).
 *
 * Shows all subscriptions/invoices regardless of type/visibility.
 * Requires the 'owner' role (Filament Shield).
 */
class OfficePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('office')
            ->path('office')
            ->login()
            ->brandName('Julius Fitness Gym — Office')
            ->colors([
                'primary' => [
                    50 => '#fff5f0',
                    100 => '#ffe8dc',
                    200 => '#ffd0bc',
                    300 => '#ffb199',
                    400 => '#ff8a66',
                    500 => '#ff5a1f',
                    600 => '#e84e15',
                    700 => '#c43f10',
                    800 => '#9a3210',
                    900 => '#7a2a0e',
                    950 => '#421408',
                ],
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->darkMode(true)
            ->defaultThemeMode(ThemeMode::Dark)
            ->sidebarWidth('12rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Office/Pages'), for: 'App\\Filament\\Office\\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->plugins([FilamentShieldPlugin::make()
                ->navigationIcon(fn (): null => null)
                ->activeNavigationIcon(fn (): null => null)])
            ->bootUsing(fn (): string => AppLocale::apply())
            ->middleware([SetAppLocale::class], isPersistent: true)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): HtmlString => new HtmlString(
                    Blade::render('@livewire(\App\Filament\Livewire\LocaleSwitcher::class, [], key(\'locale-switcher\'))').
                    Blade::render('@livewire(\App\Filament\Livewire\ThemeSwitcher::class, [], key(\'theme-switcher\'))')
                ),
            );
    }
}
