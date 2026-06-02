<?php

namespace App\Providers\Filament;

use App\Filament\Office\Pages\Dashboard;
use App\Filament\Pages\Settings;
use Filament\Navigation\NavigationBuilder;
use Filament\Panel;

/**
 * Front-desk Filament panel — same theme and navigation as admin.
 */
class OfficePanelProvider extends AdminPanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->sharedPanel($panel)
            ->default(false)
            ->id('office')
            ->path('office')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Office/Pages'), for: 'App\\Filament\\Office\\Pages')
            ->pages([
                Dashboard::class,
                Settings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->plugins($this->shieldPlugins())
            ->navigation(fn (NavigationBuilder $builder): NavigationBuilder => $this->buildNavigation($builder, 'office'));
    }
}
