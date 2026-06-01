<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\Expense;
use App\Models\Invoice;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialMetricsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 2;

    protected function getStats(): array
    {
        $start = CarbonImmutable::parse($this->filters['startDate'] ?? now()->subDays(6));
        $end = CarbonImmutable::parse($this->filters['endDate'] ?? now());

        $revenue = Invoice::whereBetween('date', [$start, $end])
            ->sum('paid_amount');

        $outstanding = Invoice::whereBetween('date', [$start, $end])
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->sum('due_amount');

        $expenses = Expense::whereBetween('date', [$start, $end])
            ->sum('amount');

        $currency = config('app.currency', 'USD');

        return [
            Stat::make(__('app.widgets.financial.revenue'), number_format((float) $revenue, 2).' '.$currency)
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make(__('app.widgets.financial.outstanding'), number_format((float) $outstanding, 2).' '.$currency)
                ->icon('heroicon-o-exclamation-circle')
                ->color($outstanding > 0 ? 'warning' : 'success'),

            Stat::make(__('app.widgets.financial.expenses'), number_format((float) $expenses, 2).' '.$currency)
                ->icon('heroicon-o-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
