<?php

namespace App\Filament\Office\Pages;

use App\Filament\Widgets\TodayCheckinsStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Illuminate\Contracts\View\View;

/**
 * Front-desk dashboard focused on today's check-ins.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $panel = 'office';

    protected static string $routePath = '/';

    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('app.office.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.navigation.office');
    }

    public function getHeader(): ?View
    {
        return null;
    }

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getWidgetsContentComponent(): Component
    {
        return Grid::make(1)->schema(
            $this->getWidgetsSchemaComponents([
                TodayCheckinsStatsWidget::class,
            ]),
        );
    }
}
