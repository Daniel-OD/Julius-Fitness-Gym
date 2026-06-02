<?php

namespace App\Filament\Widgets\Billing;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Helpers\Helpers;
use App\Models\Subscription;
use App\Services\Analytics\AnalyticsService;
use App\Support\Analytics\AnalyticsDateRange;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Subscriptions started in the selected range that have no invoice (cash not in invoice transactions).
 */
class UninvoicedSubscriptionsTableWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = -34;

    protected static ?string $heading = null;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    protected function resolveRange(): AnalyticsDateRange
    {
        $filters = $this->pageFilters ?? [];

        if (filled($filters['period'] ?? null) || filled($filters['startDate'] ?? null)) {
            return AnalyticsDateRange::fromFilters($filters);
        }

        $today = CarbonImmutable::today(AppConfig::timezone());

        return new AnalyticsDateRange($today->startOfMonth(), $today->endOfDay());
    }

    public function table(Table $table): Table
    {
        $range = $this->resolveRange();
        $total = app(AnalyticsService::class)->uninvoicedSubscriptionsCollected($range);

        return $table
            ->heading(__('app.billing.uninvoiced_subscriptions_heading'))
            ->description(__('app.billing.uninvoiced_subscriptions_description', [
                'amount' => Helpers::formatCurrency($total),
            ]))
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->query(fn (): Builder => app(AnalyticsService::class)
                ->uninvoicedSubscriptionsQuery($range)
                ->with(['member', 'plan'])
                ->latest('start_date'))
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('app.fields.member'))
                    ->description(fn (Subscription $record): string => $record->member?->code ?? '—')
                    ->url(fn (Subscription $record): string => SubscriptionResource::getUrl('view', ['record' => $record])),
                TextColumn::make('plan.name')
                    ->label(__('app.resources.plans.singular')),
                TextColumn::make('start_date')
                    ->label(__('app.fields.start_date'))
                    ->date()
                    ->timezone(AppConfig::timezone()),
                TextColumn::make('plan.amount')
                    ->label(__('app.fields.amount'))
                    ->alignRight()
                    ->state(fn (Subscription $record): string => Helpers::formatCurrency((float) ($record->plan?->amount ?? 0))),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge(),
            ])
            ->emptyStateHeading(__('app.billing.no_uninvoiced_subscriptions'))
            ->emptyStateDescription(__('app.billing.no_uninvoiced_subscriptions_description'));
    }
}
