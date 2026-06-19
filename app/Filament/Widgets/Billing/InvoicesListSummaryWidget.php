<?php

namespace App\Filament\Widgets\Billing;

use App\Helpers\Helpers;
use App\Services\Analytics\AnalyticsService;
use App\Support\Analytics\AnalyticsDateRange;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Billing summary on the invoices list (current month, includes uninvoiced subscriptions).
 */
class InvoicesListSummaryWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -60;

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
        $timezone = AppConfig::timezone();
        $today = CarbonImmutable::today($timezone);
        $range = new AnalyticsDateRange($today->startOfMonth(), $today->endOfDay());
        $metrics = app(AnalyticsService::class)->financialMetrics($range);

        return [
            Stat::make(__('app.widgets.total_collected'), Helpers::formatCurrency($metrics['collected']))
                ->description(__('app.billing.collected_breakdown', [
                    'invoiced' => Helpers::formatCurrency($metrics['collected_from_invoices']),
                    'uninvoiced' => Helpers::formatCurrency($metrics['collected_from_uninvoiced']),
                ]))
                ->descriptionIcon('heroicon-o-arrow-down-tray')
                ->color('success'),
            Stat::make(__('app.billing.uninvoiced_subscriptions'), (string) $metrics['uninvoiced_subscriptions_count'])
                ->description(__('app.billing.uninvoiced_subscriptions_month_hint', [
                    'amount' => Helpers::formatCurrency($metrics['collected_from_uninvoiced']),
                ]))
                ->descriptionIcon('heroicon-o-ticket')
                ->color($metrics['uninvoiced_subscriptions_count'] > 0 ? 'warning' : 'gray'),
            Stat::make(__('app.widgets.outstanding_payments'), Helpers::formatCurrency($metrics['outstanding']))
                ->description(__('app.billing.outstanding_invoices_hint'))
                ->descriptionIcon('heroicon-o-clock')
                ->color($metrics['outstanding'] > 0 ? 'danger' : 'gray'),
        ];
    }
}
