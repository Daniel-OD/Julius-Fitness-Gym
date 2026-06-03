<?php

namespace App\Filament\Widgets\Office;

use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only list of expired subscriptions for the front desk.
 */
class OfficeExpiredSubscriptionsWidget extends TableWidget
{
    protected static ?int $sort = -40;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return Builder<Subscription>
     */
    protected function getExpiredQuery(): Builder
    {
        $today = CarbonImmutable::today(AppConfig::timezone())->toDateString();

        return Subscription::query()
            ->with(['member', 'plan'])
            ->whereDate('end_date', '<', $today)
            ->whereDoesntHave('renewals')
            ->whereNotIn('status', ['renewed', 'cancelled'])
            ->orderByDesc('end_date');
    }

    private function expiredDays(Subscription $record): int
    {
        $today = CarbonImmutable::today(AppConfig::timezone());
        $endDate = CarbonImmutable::parse($record->end_date, AppConfig::timezone())->startOfDay();

        return (int) max($endDate->diffInDays($today, false), 0);
    }

    private function formatDayCount(int $days): string
    {
        $unit = $days === 1 ? __('app.units.day') : __('app.units.days');

        return "{$days} {$unit}";
    }

    private function expiredBadgeColor(int $days): string
    {
        if ($days > 30) {
            return 'danger';
        }

        if ($days >= 15) {
            return 'warning';
        }

        return 'gray';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.office.expired_subscriptions'))
            ->query(fn (): Builder => $this->getExpiredQuery())
            ->extraAttributes(['class' => 'office-expired-table'])
            ->recordClasses(fn (): string => 'office-table-row')
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('app.fields.member'))
                    ->description(fn (Subscription $record): string => (string) ($record->member->code ?? ''))
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('plan.name')
                    ->label(__('app.fields.plan'))
                    ->badge()
                    ->color('gray')
                    ->wrap(),
                TextColumn::make('end_date')
                    ->label(__('app.fields.end_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('days_since')
                    ->label(__('app.office.expired_since'))
                    ->alignEnd()
                    ->badge()
                    ->color(fn (Subscription $record): string => $this->expiredBadgeColor($this->expiredDays($record)))
                    ->extraAttributes(fn (Subscription $record): array => $this->expiredDays($record) < 15
                        ? ['class' => 'office-expiry-badge office-expiry-badge--recent']
                        : [])
                    ->state(fn (Subscription $record): string => $this->formatDayCount($this->expiredDays($record))),
            ])
            ->emptyStateHeading(__('app.office.no_expired_subscriptions'))
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
    }
}
