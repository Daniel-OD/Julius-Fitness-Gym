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
 * Read-only list of subscriptions expiring within the next 1–7 days.
 *
 * Date-driven (independent of the status-sync command). Employees see who is
 * about to lapse so they can prompt a renewal at the desk, but cannot manage
 * anything here.
 */
class OfficeExpiringSoonWidget extends TableWidget
{
    protected static ?int $sort = -42;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    /**
     * Badge color for the remaining-days figure.
     * red <= 2 days, orange 3–5 days, yellow 6–7 days.
     */
    public static function daysLeftColor(int $days): string
    {
        return match (true) {
            $days <= 2 => 'danger',
            $days <= 5 => 'warning',
            default => 'gray',
        };
    }

    /**
     * @return Builder<Subscription>
     */
    public function getExpiringSoonQuery(): Builder
    {
        $today = CarbonImmutable::today(AppConfig::timezone());
        $start = $today->addDay()->toDateString();   // tomorrow (>= 1 day out)
        $end = $today->addDays(7)->toDateString();    // up to 7 days out

        return Subscription::query()
            ->with(['member', 'plan'])
            ->whereDate('end_date', '>=', $start)
            ->whereDate('end_date', '<=', $end)
            ->whereDoesntHave('renewals')
            ->whereNotIn('status', ['renewed', 'cancelled'])
            ->orderBy('end_date');
    }

    public static function daysLeftFor(Subscription $subscription): int
    {
        $today = CarbonImmutable::today(AppConfig::timezone());
        $endDate = CarbonImmutable::parse($subscription->end_date, AppConfig::timezone())->startOfDay();

        return (int) max($today->diffInDays($endDate, false), 0);
    }

    private function formatDayCount(int $days): string
    {
        $unit = $days === 1 ? __('app.units.day') : __('app.units.days');

        return "{$days} {$unit}";
    }

    #[\Override]
    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.office.expiring_soon'))
            ->query(fn (): Builder => $this->getExpiringSoonQuery())
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
                TextColumn::make('days_left')
                    ->label(__('app.widgets.days_left'))
                    ->badge()
                    ->color(fn (Subscription $record): string => self::daysLeftColor(self::daysLeftFor($record)))
                    ->alignRight()
                    ->state(fn (Subscription $record): string => $this->formatDayCount(self::daysLeftFor($record))),
            ])
            ->emptyStateHeading(__('app.office.no_expiring_soon'))
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
    }
}
