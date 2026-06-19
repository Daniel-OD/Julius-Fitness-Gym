<?php

namespace App\Filament\Widgets;

use App\Helpers\Helpers;
use App\Services\Analytics\AnalyticsService;
use App\Support\Analytics\AnalyticsDateRange;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Primary dashboard KPIs: active members, expiring subscriptions, monthly revenue.
 */
class GymOverviewStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -50;

    protected int|string|array $columnSpan = 'full';

    /**
     * @var int | array<string, ?int> | null
     */
    protected int|array|null $columns = 3;

    /**
     * @return array<int, Stat>
     */
    #[\Override]
    protected function getStats(): array
    {
        $service = app(AnalyticsService::class);
        $timezone = AppConfig::timezone();
        $today = CarbonImmutable::today($timezone);

        $todayRange = new AnalyticsDateRange($today->startOfDay(), $today->endOfDay());
        $membership = $service->membershipMetrics($todayRange);

        $monthRange = new AnalyticsDateRange(
            $today->startOfMonth(),
            $today->endOfMonth(),
        );
        $financial = $service->financialMetrics($monthRange);
        $expiringCount = $service->expiringSubscriptionsCount($today);

        $monthLabel = $today->translatedFormat('F Y');

        return [
            Stat::make(__('app.widgets.active_members'), (string) $membership['active_members'])
                ->description(__('app.widgets.active_members_hint'))
                ->descriptionIcon('heroicon-o-user-group')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->chart([7, 4, 6, 8, 5, 9, $membership['active_members'] % 10 + 3]),
            Stat::make(__('app.widgets.expiring_soon'), (string) $expiringCount)
                ->description(__('app.widgets.expiring_soon_hint', [
                    'days' => (string) Helpers::getSubscriptionExpiringDays(),
                ]))
                ->descriptionIcon('heroicon-o-clock')
                ->icon('heroicon-o-clock')
                ->color($expiringCount > 0 ? 'danger' : 'gray')
                ->chart([2, 3, 2, 4, 3, $expiringCount, 2]),
            Stat::make(__('app.widgets.monthly_revenue'), Helpers::formatCurrency($financial['collected']))
                ->description(
                    $financial['collected_from_uninvoiced'] > 0
                        ? __('app.billing.collected_breakdown', [
                            'invoiced' => Helpers::formatCurrency($financial['collected_from_invoices']),
                            'uninvoiced' => Helpers::formatCurrency($financial['collected_from_uninvoiced']),
                        ])
                        : __('app.widgets.monthly_revenue_hint', ['month' => $monthLabel]),
                )
                ->descriptionIcon('heroicon-o-banknotes')
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->chart([4, 5, 6, 5, 7, 8, 6]),
        ];
    }
}
