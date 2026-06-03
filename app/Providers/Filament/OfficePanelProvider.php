<?php

namespace App\Providers\Filament;

use App\Filament\Auth\ForcePasswordChange;
use App\Filament\Office\Pages\Dashboard;
use App\Filament\Resources\CheckIns\CheckInResource;
use Filament\Navigation\NavigationBuilder;
use Filament\Panel;

/**
 * Front-desk Filament panel for employees.
 *
 * Deliberately minimal: only the check-in resource and a dashboard limited to
 * check-in/out activity, expired subscriptions, and the day's collections.
 * No financial reports, member/billing management, or settings are exposed —
 * resources are registered explicitly (not auto-discovered) so employees can
 * neither see nor reach management pages, even by direct URL.
 */
class OfficePanelProvider extends AdminPanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->sharedPanel($panel)
            ->default(false)
            ->id('office')
            ->path('office')
            ->resources([
                CheckInResource::class,
            ])
            ->pages([
                ForcePasswordChange::class,
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            // Shield plugin is intentionally omitted: it would expose the Roles
            // management resource. Permission enforcement still works through
            // policies (Gate), which are panel-independent.
            ->navigation(fn (NavigationBuilder $builder): NavigationBuilder => $this->buildOfficeNavigation($builder));
    }

    /**
     * Minimal front-desk navigation: dashboard + check-ins only.
     */
    protected function buildOfficeNavigation(NavigationBuilder $builder): NavigationBuilder
    {
        return $builder
            ->items([
                ...Dashboard::getNavigationItems(),
                ...CheckInResource::getNavigationItems(),
            ]);
    }
}
