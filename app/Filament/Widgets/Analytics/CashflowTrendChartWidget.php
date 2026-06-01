<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\Expense;
use App\Models\Invoice;
use Carbon\CarbonImmutable;
use Filament\Widgets\BarChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CashflowTrendChartWidget extends BarChartWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = null;

    public function getHeading(): string
    {
        return __('app.widgets.cashflow.heading');
    }

    protected function getData(): array
    {
        $start = CarbonImmutable::parse($this->filters['startDate'] ?? now()->subDays(6));
        $end = CarbonImmutable::parse($this->filters['endDate'] ?? now());

        $days = $start->diffInDays($end);
        $groupBy = $days > 60 ? 'month' : ($days > 14 ? 'week' : 'day');

        $labels = [];
        $revenue = [];
        $expenses = [];

        $current = $start;

        while ($current->lte($end)) {
            if ($groupBy === 'day') {
                $periodStart = $current->startOfDay();
                $periodEnd = $current->endOfDay();
                $labels[] = $current->format('M d');
                $current = $current->addDay();
            } elseif ($groupBy === 'week') {
                $periodStart = $current->startOfWeek();
                $periodEnd = $current->endOfWeek()->min($end);
                $labels[] = $current->format('M d');
                $current = $current->addWeek();
            } else {
                $periodStart = $current->startOfMonth();
                $periodEnd = $current->endOfMonth()->min($end);
                $labels[] = $current->format('M Y');
                $current = $current->addMonth();
            }

            $revenue[] = (float) Invoice::whereBetween('date', [$periodStart, $periodEnd])
                ->sum('paid_amount');

            $expenses[] = (float) Expense::whereBetween('date', [$periodStart, $periodEnd])
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => __('app.widgets.cashflow.revenue'),
                    'data' => $revenue,
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => __('app.widgets.cashflow.expenses'),
                    'data' => $expenses,
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
