<?php

namespace App\Filament\Widgets\Analytics;

use App\Enums\Status;
use App\Models\Member;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MembershipMetricsWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $start = CarbonImmutable::parse($this->filters['startDate'] ?? now()->subDays(6));
        $end = CarbonImmutable::parse($this->filters['endDate'] ?? now());

        $totalMembers = Member::count();

        $activeMembers = Member::where('status', Status::Active)->count();

        $newMembers = Member::whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])->count();

        $expiringCount = Subscription::where('status', Status::Active)
            ->whereBetween('end_date', [now(), now()->addDays(7)])
            ->count();

        $newSubscriptions = Subscription::whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])->count();

        return [
            Stat::make(__('app.widgets.membership.total_members'), $totalMembers)
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make(__('app.widgets.membership.active_members'), $activeMembers)
                ->icon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make(__('app.widgets.membership.new_members'), $newMembers)
                ->icon('heroicon-o-user-plus')
                ->description(__('app.widgets.membership.in_period'))
                ->color('info'),

            Stat::make(__('app.widgets.membership.new_subscriptions'), $newSubscriptions)
                ->icon('heroicon-o-document-plus')
                ->description(__('app.widgets.membership.in_period'))
                ->color('info'),

            Stat::make(__('app.widgets.membership.expiring_soon'), $expiringCount)
                ->icon('heroicon-o-clock')
                ->description(__('app.widgets.membership.next_7_days'))
                ->color($expiringCount > 0 ? 'warning' : 'success'),
        ];
    }
}
