<?php

namespace App\Filament\Widgets\Office;

use App\Helpers\Helpers;
use App\Models\CheckIn;
use App\Services\Analytics\AnalyticsService;
use App\Support\Analytics\AnalyticsDateRange;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Front-desk "today" snapshot: check-ins, check-outs and the day's collections.
 */
class OfficeTodayStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -50;

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    /**
     * @var int | array<string, ?int> | null
     */
    protected int|array|null $columns = [
        'default' => 1,
        'sm' => 3,
    ];

    /**
     * @return array<int, Stat>
     */
    #[\Override]
    protected function getStats(): array
    {
        $timezone = AppConfig::timezone();
        $today = CarbonImmutable::today($timezone);
        $range = new AnalyticsDateRange($today->startOfDay(), $today->endOfDay());

        $checkInsToday = CheckIn::query()
            ->whereDate('checked_in_at', $today->toDateString())
            ->count();

        $checkOutsToday = CheckIn::query()
            ->whereDate('checked_out_at', $today->toDateString())
            ->count();

        $collectedToday = app(AnalyticsService::class)->financialMetrics($range)['collected'];

        return [
            Stat::make(__('app.office.checkins_today'), (string) $checkInsToday)
                ->description(__('app.office.checkins_today_hint'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->icon('heroicon-o-arrow-right-end-on-rectangle')
                ->color('success')
                ->extraAttributes(['class' => 'office-stat office-stat--checkin']),

            Stat::make(__('app.office.checkouts_today'), (string) $checkOutsToday)
                ->description(__('app.office.checkouts_today_hint'))
                ->descriptionIcon('heroicon-m-arrow-right-start-on-rectangle')
                ->icon('heroicon-o-arrow-left-start-on-rectangle')
                ->color('info')
                ->extraAttributes(['class' => 'office-stat office-stat--checkout']),

            Stat::make(__('app.office.collections_today'), Helpers::formatCurrency((float) $collectedToday))
                ->description(__('app.office.collections_today_hint'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->extraAttributes(['class' => 'office-stat office-stat--collections']),
        ];
    }
}
