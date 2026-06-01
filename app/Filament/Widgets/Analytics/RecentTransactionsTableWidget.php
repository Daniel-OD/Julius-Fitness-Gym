<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\InvoiceTransaction;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class RecentTransactionsTableWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        $start = CarbonImmutable::parse($this->filters['startDate'] ?? now()->subDays(6));
        $end = CarbonImmutable::parse($this->filters['endDate'] ?? now());

        $currency = config('app.currency', 'USD');

        return $table
            ->heading(__('app.widgets.transactions.heading'))
            ->query(
                InvoiceTransaction::with('invoice')
                    ->whereBetween('occurred_at', [$start->startOfDay(), $end->endOfDay()])
                    ->latest('occurred_at')
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('occurred_at')
                    ->label(__('app.widgets.transactions.date'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('invoice.number')
                    ->label(__('app.widgets.transactions.invoice'))
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('app.widgets.transactions.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'payment' => 'success',
                        'refund' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label(__('app.widgets.transactions.amount'))
                    ->money($currency)
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label(__('app.widgets.transactions.method'))
                    ->sortable(),
            ]);
    }
}
