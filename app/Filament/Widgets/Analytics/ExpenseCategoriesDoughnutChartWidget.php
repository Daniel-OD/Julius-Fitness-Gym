<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\Expense;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\DoughnutChartWidget;

class ExpenseCategoriesDoughnutChartWidget extends DoughnutChartWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 2;

    protected ?string $heading = null;

    public function getHeading(): string
    {
        return __('app.widgets.expense_categories.heading');
    }

    protected function getData(): array
    {
        $start = CarbonImmutable::parse($this->filters['startDate'] ?? now()->subDays(6));
        $end = CarbonImmutable::parse($this->filters['endDate'] ?? now());

        $categories = Expense::whereBetween('date', [$start, $end])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category');

        return [
            'datasets' => [
                [
                    'data' => $categories->values()->map(fn ($v) => (float) $v)->all(),
                    'backgroundColor' => [
                        '#0ea5e9', '#f59e0b', '#10b981', '#ef4444',
                        '#8b5cf6', '#f97316', '#06b6d4', '#84cc16',
                    ],
                ],
            ],
            'labels' => $categories->keys()->map(fn ($k) => $k ?? __('app.widgets.expense_categories.uncategorized'))->all(),
        ];
    }
}
