<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Settings;
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
            ->navigation(fn (NavigationBuilder $builder) => $this->buildNavigation($builder));
    }

    /**
     * Configure the base panel options.
     */
    public function basePanel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->brandName('Julius Fitness Gym')
            ->brandLogo(asset('images/brand/julius-fitness-logo.svg'))
            ->brandLogoHeight('2.75rem')
            ->unsavedChangesAlerts()
            ->colors($this->colors())
            ->darkMode(false)
            ->sidebarWidth('12rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                Settings::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->databaseNotifications()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): HtmlString => new HtmlString(
                    Blade::render('@livewire(\App\Filament\Livewire\LocaleSwitcher::class, [], key(\'locale-switcher\'))')
                ),
            );
    }

    /**
     * Build grouped navigation for the admin panel.
     */
    protected function buildNavigation(NavigationBuilder $builder): NavigationBuilder
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
                    ->url(fn () => Dashboard::getUrl())
                    ->isActiveWhen(fn () => request()->routeIs('filament.admin.pages.dashboard'))
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
                50 => '#faf6e8',
                100 => '#f0e6c4',
                200 => '#e5d49a',
                300 => '#d4af37',
                400 => '#c9a227',
                500 => '#b8941f',
                600 => '#9a7a19',
                700 => '#7c6114',
                800 => '#5e4a10',
                900 => '#3d310b',
                950 => '#241c06',
            ],
            'danger' => [
                50 => '#fef2f3',
                100 => '#fde3e6',
                200 => '#fbb8c0',
                300 => '#f78997',
                400 => '#ef4d63',
                500 => '#c41e3a',
                600 => '#9f1830',
                700 => '#7a1226',
                800 => '#560d1b',
                900 => '#350810',
                950 => '#1f0409',
            ],
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'success' => Color::Emerald,
            'warning' => Color::Amber,
        ];
    }
}
