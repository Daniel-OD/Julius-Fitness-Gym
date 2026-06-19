<?php

namespace App\Filament\Widgets;

use App\Services\CheckIns\CheckInService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Live count of member check-ins for today (polls every 30 seconds).
 */
class TodayCheckinsStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -49;

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    /**
     * @var int | array<string, ?int> | null
     */
    protected int|array|null $columns = 1;

    /**
     * @return array<int, Stat>
     */
    #[\Override]
    protected function getStats(): array
    {
        $count = app(CheckInService::class)->todayCheckInCount();

        return [
            Stat::make(__('app.widgets.today_checkins'), (string) $count)
                ->description($count === 0
                    ? __('app.empty.no_checkins_today')
                    : __('app.widgets.today_checkins_hint'))
                ->descriptionIcon('heroicon-o-arrow-right-end-on-rectangle')
                ->icon('heroicon-o-arrow-right-end-on-rectangle')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'fi-wi-stats-overview-stat-value text-4xl font-semibold tracking-tight',
                ]),
        ];
    }
}
