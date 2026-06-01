<?php

namespace App\Filament\Widgets\Analytics;

use App\Enums\Status;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;

class MembershipOverviewSubscriptionsTableWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        $start = CarbonImmutable::parse($this->filters['startDate'] ?? now()->subDays(6));
        $end = CarbonImmutable::parse($this->filters['endDate'] ?? now());

        return $table
            ->heading(__('app.widgets.subscriptions.heading'))
            ->query(
                Subscription::with(['member', 'plan'])
                    ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                    ->latest()
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('app.widgets.subscriptions.member'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plan.name')
                    ->label(__('app.widgets.subscriptions.plan'))
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label(__('app.widgets.subscriptions.start_date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label(__('app.widgets.subscriptions.end_date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('app.widgets.subscriptions.status'))
                    ->badge()
                    ->color(fn (Status $state): string => $state->getColor()),
            ]);
    }
}
