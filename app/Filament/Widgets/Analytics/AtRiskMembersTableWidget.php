<?php

namespace App\Filament\Widgets\Analytics;

use App\Filament\Resources\Members\MemberResource;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Surfaces active members who haven't checked in for ABSENT_DAYS days.
 *
 * These are members who are still paying but quietly losing motivation — the
 * highest-risk segment for churn at renewal. The gym has all the data; this
 * widget just cross-references subscriptions with check-ins for the first time.
 */
class AtRiskMembersTableWidget extends TableWidget
{
    protected static ?int $sort = -38;

    protected static ?string $heading = null;

    /** Members absent for this many days are considered at-risk. */
    private const ABSENT_DAYS = 14;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    /**
     * @return Builder<Member>
     */
    protected function getTableQuery(): Builder
    {
        $timezone = AppConfig::timezone();
        $today = CarbonImmutable::today($timezone)->toDateString();
        $threshold = CarbonImmutable::now($timezone)->subDays(self::ABSENT_DAYS);

        return Member::query()
            ->whereHas('subscriptions', fn (Builder $q): Builder => $q
                ->where('status', Subscription::STATUS_ONGOING ?? 'ongoing')
                ->whereDate('end_date', '>=', $today)
            )
            ->whereDoesntHave('checkIns', fn (Builder $q): Builder => $q
                ->where('checked_in_at', '>=', $threshold)
            )
            ->addSelect([
                'last_checkin_at' => CheckIn::select('checked_in_at')
                    ->whereColumn('member_id', 'members.id')
                    ->whereNull('deleted_at')
                    ->latest('checked_in_at')
                    ->limit(1),
            ])
            ->with([
                'subscriptions' => fn (HasMany $q): HasMany => $q
                    ->where('status', 'ongoing')
                    ->whereDate('end_date', '>=', $today)
                    ->with('plan')
                    ->latest('end_date'),
            ])
            ->orderBy('last_checkin_at', 'asc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.widgets.at_risk_members'))
            ->description(__('app.widgets.at_risk_members_description', ['days' => self::ABSENT_DAYS]))
            ->query(fn (): Builder => $this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label(__('app.fields.member'))
                    ->description(fn (Member $record): string => (string) ($record->code ?? ''))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('last_checkin_at')
                    ->label(__('app.widgets.at_risk_last_visit'))
                    ->state(fn (Member $record): string => $this->formatLastVisit($record))
                    ->badge()
                    ->color(fn (Member $record): string => $this->lastVisitColor($record)),
                TextColumn::make('days_absent')
                    ->label(__('app.widgets.at_risk_days_absent'))
                    ->state(fn (Member $record): string => $this->formatDaysAbsent($record))
                    ->badge()
                    ->color(fn (Member $record): string => $this->daysAbsentColor($record))
                    ->alignEnd(),
                TextColumn::make('subscription_end')
                    ->label(__('app.fields.end_date'))
                    ->state(fn (Member $record): string => $this->formatSubscriptionEnd($record))
                    ->sortable(false),
                TextColumn::make('plan_name')
                    ->label(__('app.fields.plan'))
                    ->state(fn (Member $record): string => (string) ($record->subscriptions->first()?->plan?->name ?? '—'))
                    ->wrap(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Member $record): string => MemberResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateIcon('heroicon-o-check-badge')
            ->emptyStateHeading(__('app.widgets.no_at_risk_members'))
            ->emptyStateDescription(__('app.widgets.no_at_risk_members_description', ['days' => self::ABSENT_DAYS]))
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
    }

    private function formatLastVisit(Member $record): string
    {
        $raw = $record->last_checkin_at;

        if (empty($raw)) {
            return __('app.widgets.at_risk_never_visited');
        }

        return CarbonImmutable::parse((string) $raw, AppConfig::timezone())->format('d M Y');
    }

    private function lastVisitColor(Member $record): string
    {
        if (empty($record->last_checkin_at)) {
            return 'danger';
        }

        $days = (int) CarbonImmutable::parse((string) $record->last_checkin_at, AppConfig::timezone())
            ->diffInDays(CarbonImmutable::now(AppConfig::timezone()));

        return $days >= 30 ? 'danger' : 'warning';
    }

    private function formatDaysAbsent(Member $record): string
    {
        $raw = $record->last_checkin_at;

        if (empty($raw)) {
            $sub = $record->subscriptions->first();
            if ($sub && $sub->start_date) {
                $days = (int) CarbonImmutable::parse($sub->start_date, AppConfig::timezone())
                    ->diffInDays(CarbonImmutable::now(AppConfig::timezone()));

                return $days.' '.($days === 1 ? __('app.units.day') : __('app.units.days'));
            }

            return '—';
        }

        $days = (int) CarbonImmutable::parse((string) $raw, AppConfig::timezone())
            ->diffInDays(CarbonImmutable::now(AppConfig::timezone()));

        return $days.' '.($days === 1 ? __('app.units.day') : __('app.units.days'));
    }

    private function daysAbsentColor(Member $record): string
    {
        if (empty($record->last_checkin_at)) {
            return 'danger';
        }

        $days = (int) CarbonImmutable::parse((string) $record->last_checkin_at, AppConfig::timezone())
            ->diffInDays(CarbonImmutable::now(AppConfig::timezone()));

        return $days >= 30 ? 'danger' : 'warning';
    }

    private function formatSubscriptionEnd(Member $record): string
    {
        $endDate = $record->subscriptions->first()?->end_date;

        return $endDate ? $endDate->format('d M Y') : '—';
    }
}
