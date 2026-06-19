<?php

namespace App\Filament\Office\Pages;

use App\Filament\Widgets\Office\OfficeExpiredSubscriptionsWidget;
use App\Filament\Widgets\Office\OfficeExpiringSoonWidget;
use App\Filament\Widgets\Office\OfficePresentNowWidget;
use App\Filament\Widgets\Office\OfficeTodayStatsWidget;
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

    #[\Override]
    public function getTitle(): string
    {
        return __('app.office.title');
    }

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return __('app.navigation.office');
    }

    public function getHeader(): ?View
    {
        return null;
    }

    #[\Override]
    public function getColumns(): int|array
    {
        return 1;
    }

    #[\Override]
    public function getWidgetsContentComponent(): Component
    {
        return Grid::make(1)
            ->extraAttributes(['class' => 'office-dashboard'])
            ->schema(
                $this->getWidgetsSchemaComponents([
                    OfficeTodayStatsWidget::class,
                    OfficePresentNowWidget::class,
                    OfficeExpiringSoonWidget::class,
                    OfficeExpiredSubscriptionsWidget::class,
                ]),
            );
    }
}
