<?php

namespace App\Providers\Filament;

use App\Filament\Office\Pages\Dashboard as OfficeDashboard;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Settings;
use App\Filament\Resources\CheckIns\CheckInResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Expenses\ExpenseResource;
use App\Filament\Resources\FollowUps\FollowUpResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Plans\PlanResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Users\UserResource;
use App\Http\Middleware\SetAppLocale;
use App\Support\AppLocale;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
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
 * Filament panel provider for the main admin panel.
 */
class AdminPanelProvider extends PanelProvider
{
    /**
     * Configure the panel.
     */
    public function panel(Panel $panel): Panel
    {
        return $this->basePanel($panel)
            ->navigation(fn (NavigationBuilder $builder) => $this->buildNavigation($builder, 'admin'));
    }

    /**
     * Shared Filament panel options for admin and office panels.
     */
    protected function sharedPanel(Panel $panel): Panel
    {
        return $panel
            ->login()
            ->passwordReset()
            ->brandName('Julius Fitness Gym')
            ->unsavedChangesAlerts()
            ->colors($this->colors())
            ->darkMode(true)
            ->defaultThemeMode(ThemeMode::Dark)
            ->sidebarWidth('12rem')
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
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): HtmlString => new HtmlString(
                    Blade::render('@livewire(\App\Filament\Livewire\SubscriptionExpirationNotifications::class, [], key(\'subscription-expiration-notifications\'))').
                    Blade::render('@livewire(\App\Filament\Livewire\LocaleSwitcher::class, [], key(\'locale-switcher\'))').
                    Blade::render('@livewire(\App\Filament\Livewire\ThemeSwitcher::class, [], key(\'theme-switcher\'))')
                ),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): HtmlString => new HtmlString(Blade::render('<x-studio.signature variant="login" />')),
            )
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): HtmlString => new HtmlString(
                    Blade::render('<div class="px-4 pb-2"><x-studio.signature /></div>')
                ),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString(Blade::render('<x-studio.html-comment />')),
            );
    }

    /**
     * Configure the admin panel.
     */
    protected function basePanel(Panel $panel): Panel
    {
        return $this->sharedPanel($panel)
            ->default()
            ->id('admin')
            ->path('admin')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                Settings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->plugins($this->shieldPlugins());
    }

    /**
     * @return array<int, mixed>
     */
    protected function shieldPlugins(): array
    {
        return [
            FilamentShieldPlugin::make()
                ->navigationIcon(fn (): null => null)
                ->activeNavigationIcon(fn (): null => null),
        ];
    }

    /**
     * Build grouped navigation for the admin panel.
     */
    protected function buildNavigation(NavigationBuilder $builder, string $panel = 'admin'): NavigationBuilder
    {
        $administration = [
            ...Settings::getNavigationItems(),
            ...UserResource::getNavigationItems(),
            ...RoleResource::getNavigationItems(),
        ];

        $sales = [
            ...EnquiryResource::getNavigationItems(),
            ...FollowUpResource::getNavigationItems(),
        ];

        $billing = [
            ...InvoiceResource::getNavigationItems(),
            ...ExpenseResource::getNavigationItems(),
        ];

        $memberships = [
            ...MemberResource::getNavigationItems(),
            ...PlanResource::getNavigationItems(),
            ...ServiceResource::getNavigationItems(),
            ...SubscriptionResource::getNavigationItems(),
            ...CheckInResource::getNavigationItems(),
        ];

        return $builder
            ->groups([
                NavigationGroup::make(__('app.navigation.groups.sales'))
                    ->icon('heroicon-o-shopping-cart')
                    ->items($sales)
                    ->collapsed(false),

                NavigationGroup::make(__('app.navigation.groups.memberships'))
                    ->icon('heroicon-o-user-group')
                    ->items($memberships)
                    ->collapsed(false),

                NavigationGroup::make(__('app.navigation.groups.billing'))
                    ->icon('heroicon-o-document-text')
                    ->items($billing)
                    ->collapsed(false),

                NavigationGroup::make(__('app.navigation.groups.administration'))
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->items($administration)
                    ->collapsed(false),
            ])
            ->item(
                NavigationItem::make(__('app.navigation.dashboard'))
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (): string => $panel === 'office'
                        ? OfficeDashboard::getUrl()
                        : Dashboard::getUrl())
                    ->isActiveWhen(fn (): bool => $panel === 'office'
                        ? request()->routeIs('filament.office.pages.dashboard')
                        : request()->routeIs('filament.admin.pages.dashboard'))
            );
    }

    /**
     * Panel color palette.
     *
     * @return array<string, mixed>
     */
    protected function colors(): array
    {
        return [
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
        ];
    }
}
