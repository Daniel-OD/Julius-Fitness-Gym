<?php

namespace App\Filament\Widgets\Office;

use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only list of expired subscriptions for the front desk.
 *
 * Date-driven so it stays correct even before the status-sync command runs.
 * Employees can see who has lapsed, but cannot manage or renew here.
 */
class OfficeExpiredSubscriptionsWidget extends TableWidget
{
    protected static ?int $sort = -40;

    /**
     * @var int | string | array<string, int | null>
     */
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

    private function formatDayCount(int $days): string
    {
        $unit = $days === 1 ? __('app.units.day') : __('app.units.days');

        return "{$days} {$unit}";
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.office.expired_subscriptions'))
            ->query(fn (): Builder => $this->getExpiredQuery())
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('app.fields.member'))
                    ->description(fn (Subscription $record): string => (string) ($record->member->code ?? ''))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('plan.name')
                    ->label(__('app.fields.plan'))
                    ->wrap(),
                TextColumn::make('end_date')
                    ->label(__('app.fields.end_date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('days_since')
                    ->label(__('app.office.expired_since'))
                    ->alignRight()
                    ->state(function (Subscription $record): string {
                        $today = CarbonImmutable::today(AppConfig::timezone());
                        $endDate = CarbonImmutable::parse($record->end_date, AppConfig::timezone())->startOfDay();

                        $days = (int) max($endDate->diffInDays($today, false), 0);

                        return $this->formatDayCount($days);
                    }),
            ])
            ->emptyStateHeading(__('app.office.no_expired_subscriptions'))
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
    }
}
