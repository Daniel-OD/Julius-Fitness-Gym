<?php

namespace App\Services\Analytics;

use App\Helpers\Helpers;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceTransaction;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Support\Analytics\AnalyticsDateRange;
use App\Support\AppConfig;
use App\Support\Data;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnalyticsService
{
    private function monthGroupExpression(string $column, string $driver): string
    {
        return match ($driver) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    /**
     * @return array{
     *   net_revenue: float,
     *   collected: float,
     *   collected_from_invoices: float,
     *   collected_from_uninvoiced: float,
     *   refunds: float,
     *   discounts: float,
     *   outstanding: float,
     *   expenses: float,
     *   profit: float,
     *   uninvoiced_subscriptions_count: int,
     * }
     */
    public function financialMetrics(AnalyticsDateRange $range): array
    {
        $salesTotal = (float) Invoice::query()
            ->whereBetween('date', [$range->start->toDateString(), $range->end->toDateString()])
            ->sum('total_amount');

        $transactions = InvoiceTransaction::query()
            ->whereBetween('occurred_at', [$range->start, $range->end])
            ->selectRaw("SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) as payments_total")
            ->selectRaw("SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END) as refunds_total")
            ->first();

        $paymentsTotal = (float) ($transactions->payments_total ?? 0);
        $refundsTotal = (float) ($transactions->refunds_total ?? 0);
        $collectedFromInvoices = max($paymentsTotal - $refundsTotal, 0);
        $collectedFromUninvoiced = $this->uninvoicedSubscriptionsCollected($range);
        $uninvoicedCount = $this->uninvoicedSubscriptionsCount($range);
        $collected = $collectedFromInvoices + $collectedFromUninvoiced;
        $netRevenue = max($salesTotal + $collectedFromUninvoiced - $refundsTotal, 0);

        $discounts = (float) Invoice::query()
            ->whereBetween('date', [$range->start->toDateString(), $range->end->toDateString()])
            ->sum('discount_amount');

        $outstanding = (float) Invoice::query()
            ->whereDate('date', '<=', $range->referenceDateString())
            ->where('due_amount', '>', 0)
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->sum('due_amount');

        $expenses = (float) Expense::query()
            ->whereBetween('date', [$range->start->toDateString(), $range->end->toDateString()])
            ->sum('amount');

        return [
            'net_revenue' => $netRevenue,
            'collected' => $collected,
            'collected_from_invoices' => $collectedFromInvoices,
            'collected_from_uninvoiced' => $collectedFromUninvoiced,
            'refunds' => $refundsTotal,
            'discounts' => $discounts,
            'outstanding' => $outstanding,
            'expenses' => $expenses,
            'profit' => max($collected - $expenses, 0),
            'uninvoiced_subscriptions_count' => $uninvoicedCount,
        ];
    }

    public function uninvoicedSubscriptionsCollected(AnalyticsDateRange $range): float
    {
        return Data::float(
            $this->uninvoicedSubscriptionsQuery($range)
                ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                ->sum('plans.amount'),
        );
    }

    public function uninvoicedSubscriptionsCount(AnalyticsDateRange $range): int
    {
        return $this->uninvoicedSubscriptionsQuery($range)->count();
    }

    /**
     * @return Builder<Subscription>
     */
    public function uninvoicedSubscriptionsQuery(AnalyticsDateRange $range): Builder
    {
        return Subscription::query()
            ->withoutInvoices()
            ->whereBetween('start_date', [
                $range->start->toDateString(),
                $range->end->toDateString(),
            ]);
    }

    /**
     * @return array<string, float>
     */
    public function uninvoicedCollectedTrendByDate(AnalyticsDateRange $range): array
    {
        /** @var Collection<string, float> $rows */
        $rows = $this->uninvoicedSubscriptionsQuery($range)
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->selectRaw('subscriptions.start_date as day')
            ->selectRaw('SUM(plans.amount) as total')
            ->groupBy('subscriptions.start_date')
            ->orderBy('subscriptions.start_date')
            ->pluck('total', 'day')
            ->map(fn ($value): float => Data::float($value));

        return $rows->all();
    }

    /**
     * @return array{active_members: int, new_signups: int, renewals: int, expired_not_renewed: int}
     */
    public function membershipMetrics(AnalyticsDateRange $range): array
    {
        $referenceDate = $range->referenceDateString();

        $activeMembers = Member::query()
            ->whereHas('subscriptions', function (Builder $query) use ($referenceDate): void {
                $query
                    ->whereDate('start_date', '<=', $referenceDate)
                    ->whereDate('end_date', '>=', $referenceDate);
            })
            ->count();

        $newSignups = Member::query()
            ->whereBetween('created_at', [$range->start, $range->end])
            ->count();

        $renewals = Subscription::query()
            ->whereNotNull('renewed_from_subscription_id')
            ->whereBetween('start_date', [$range->start->toDateString(), $range->end->toDateString()])
            ->count();

        $expiredNotRenewed = Subscription::query()
            ->whereBetween('end_date', [$range->start->toDateString(), $range->end->toDateString()])
            ->whereDoesntHave('renewals')
            ->count();

        return [
            'active_members' => $activeMembers,
            'new_signups' => $newSignups,
            'renewals' => $renewals,
            'expired_not_renewed' => $expiredNotRenewed,
        ];
    }

    public function expiringSubscriptionsCount(?CarbonImmutable $today = null): int
    {
        $today ??= CarbonImmutable::today(AppConfig::timezone());
        $expiringDays = Helpers::getSubscriptionExpiringDays();
        $end = $today->addDays($expiringDays);

        return Subscription::query()
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $today->toDateString())
            ->whereDate('end_date', '<=', $end->toDateString())
            ->count();
    }

    public function overdueInvoicesCount(): int
    {
        return Invoice::query()
            ->where('status', 'overdue')
            ->where('due_amount', '>', 0)
            ->count();
    }

    /**
     * Net collected by date for the given range (payments - refunds).
     *
     * @return array<string, float> Map of `Y-m-d` => amount
     */
    public function collectedTrendByDate(AnalyticsDateRange $range): array
    {
        /** @var Collection<string, float> $rows */
        $rows = InvoiceTransaction::query()
            ->whereBetween('occurred_at', [$range->start, $range->end])
            ->selectRaw('DATE(occurred_at) as day')
            ->selectRaw("SUM(CASE WHEN type = 'payment' THEN amount WHEN type = 'refund' THEN -amount ELSE 0 END) as net")
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('net', 'day')
            ->map(fn ($value): float => Data::float($value));

        $trend = $rows->all();

        foreach ($this->uninvoicedCollectedTrendByDate($range) as $day => $amount) {
            $trend[$day] = ($trend[$day] ?? 0) + $amount;
        }

        return $trend;
    }

    /**
     * @return array<string, float>
     */
    public function collectedTrendByMonth(AnalyticsDateRange $range): array
    {
        $driver = InvoiceTransaction::query()->getModel()->getConnection()->getDriverName();
        $monthExpression = $this->monthGroupExpression('occurred_at', $driver);

        /** @var Collection<string, float> $rows */
        $rows = InvoiceTransaction::query()
            ->whereBetween('occurred_at', [$range->start, $range->end])
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw("SUM(CASE WHEN type = 'payment' THEN amount WHEN type = 'refund' THEN -amount ELSE 0 END) as net")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('net', 'month')
            ->map(fn ($value): float => Data::float($value));

        $trend = $rows->all();

        $driver = Subscription::query()->getModel()->getConnection()->getDriverName();
        $monthExpression = $this->monthGroupExpression('subscriptions.start_date', $driver);

        /** @var Collection<string, float> $uninvoiced */
        $uninvoiced = $this->uninvoicedSubscriptionsQuery($range)
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('SUM(plans.amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($value): float => Data::float($value));

        foreach ($uninvoiced as $month => $amount) {
            $trend[$month] = ($trend[$month] ?? 0) + $amount;
        }

        return $trend;
    }

    /**
     * Expense totals by date for the given range.
     *
     * @return array<string, float> Map of `Y-m-d` => amount
     */
    public function expenseTrendByDate(AnalyticsDateRange $range): array
    {
        /** @var Collection<string, float> $rows */
        $rows = Expense::query()
            ->whereBetween('date', [$range->start->toDateString(), $range->end->toDateString()])
            ->selectRaw('date as day')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->map(fn ($value): float => Data::float($value));

        return $rows->all();
    }

    /**
     * @return array<string, float>
     */
    public function expenseTrendByMonth(AnalyticsDateRange $range): array
    {
        $driver = Expense::query()->getModel()->getConnection()->getDriverName();
        $monthExpression = $this->monthGroupExpression('date', $driver);

        /** @var Collection<string, float> $rows */
        $rows = Expense::query()
            ->whereBetween('date', [$range->start->toDateString(), $range->end->toDateString()])
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->map(fn ($value): float => Data::float($value));

        return $rows->all();
    }

    /**
     * @return Collection<int, array{key: string, plan_id: int, plan_name: string, collected: float, subscriptions: int}>
     */
    public function topPlansByCollected(AnalyticsDateRange $range, int $limit = 5): Collection
    {
        /** @var Collection<int, object{plan_id:int, plan_name:string, collected:float, subscriptions:int}> $rows */
        $rows = Plan::query()
            ->select('plans.id as plan_id', 'plans.name as plan_name')
            ->leftJoin('subscriptions', 'subscriptions.plan_id', '=', 'plans.id')
            ->leftJoin('invoices', 'invoices.subscription_id', '=', 'subscriptions.id')
            ->leftJoin('invoice_transactions', function ($join) use ($range): void {
                $join
                    ->on('invoice_transactions.invoice_id', '=', 'invoices.id')
                    ->whereBetween('invoice_transactions.occurred_at', [$range->start, $range->end]);
            })
            ->selectRaw("COALESCE(SUM(CASE WHEN invoice_transactions.type = 'payment' THEN invoice_transactions.amount WHEN invoice_transactions.type = 'refund' THEN -invoice_transactions.amount ELSE 0 END), 0) as collected")
            ->selectRaw('COUNT(DISTINCT subscriptions.id) as subscriptions')
            ->groupBy('plans.id', 'plans.name')
            ->orderByDesc('collected')
            ->limit($limit)
            ->get();

        $uninvoicedByPlan = $this->uninvoicedSubscriptionsQuery($range)
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->selectRaw('plans.id as plan_id')
            ->selectRaw('SUM(plans.amount) as uninvoiced_collected')
            ->selectRaw('COUNT(subscriptions.id) as uninvoiced_count')
            ->groupBy('plans.id')
            ->get()
            ->keyBy('plan_id');

        $mapped = $rows->map(function (object $row) use ($uninvoicedByPlan): array {
            $planId = (int) $row->plan_id;
            $uninvoiced = $uninvoicedByPlan->get($planId);

            return [
                'key' => 'plan:'.$planId,
                'plan_id' => $planId,
                'plan_name' => (string) $row->plan_name,
                'collected' => (float) $row->collected + (float) ($uninvoiced->uninvoiced_collected ?? 0),
                'subscriptions' => (int) $row->subscriptions + (int) ($uninvoiced->uninvoiced_count ?? 0),
            ];
        })->keyBy('plan_id');

        foreach ($uninvoicedByPlan as $planId => $uninvoiced) {
            $planId = (int) $planId;

            if ($mapped->has($planId)) {
                continue;
            }

            $mapped->put($planId, [
                'key' => 'plan:'.$planId,
                'plan_id' => $planId,
                'plan_name' => (string) Plan::query()->whereKey($planId)->value('name'),
                'collected' => (float) $uninvoiced->uninvoiced_collected,
                'subscriptions' => (int) $uninvoiced->uninvoiced_count,
            ]);
        }

        return $mapped->sortByDesc('collected')->values()->take($limit);
    }

    /**
     * @return Collection<int, array{key: string, category: string, total: float}>
     */
    public function expenseCategoryBreakdownForChart(AnalyticsDateRange $range, int $limit = 5): Collection
    {
        /** @var Collection<int, object{category: string, total: float}> $rows */
        $rows = Expense::query()
            ->whereBetween('date', [$range->start->toDateString(), $range->end->toDateString()])
            ->select('category')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        $mapped = $rows->map(fn (object $row): array => [
            'category' => (string) $row->category,
            'total' => (float) $row->total,
        ]);

        if ($mapped->count() <= $limit) {
            return $mapped->values();
        }

        $top = $mapped->take($limit)->values();
        $otherTotal = Data::float($mapped->slice($limit)->sum('total'));

        if ($otherTotal > 0) {
            $top->push(['category' => 'Other', 'total' => $otherTotal]);
        }

        return $top;
    }
}
