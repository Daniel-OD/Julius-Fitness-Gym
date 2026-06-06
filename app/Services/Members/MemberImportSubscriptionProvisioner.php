<?php

namespace App\Services\Members;

use App\Enums\Status;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Service;
use App\Models\Subscription;
use App\Support\AppConfig;
use App\Support\Members\MemberImportValueParser;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MemberImportSubscriptionProvisioner
{
    private const DEFAULT_PLAN_DAYS = 30;

    public function __construct(
        private readonly MemberImportValueParser $parser,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public function hasSubscriptionData(array $row): bool
    {
        foreach (['plan_name', 'plan_amount', 'plan_days', 'subscription_start', 'subscription_end'] as $field) {
            if (filled($row[$field] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function provision(Member $member, array $row): bool
    {
        if (! $this->hasSubscriptionData($row)) {
            return false;
        }

        $amount = $this->parser->parseAmount($row['plan_amount'] ?? null);
        $planName = filled($row['plan_name'] ?? null)
            ? trim((string) $row['plan_name'])
            : null;

        if ($planName === null && $amount === null) {
            return false;
        }

        $startDate = $this->parser->parseDate($row['subscription_start'] ?? null)
            ?? Carbon::today(AppConfig::timezone())->toDateString();
        $endDate = $this->parser->parseDate($row['subscription_end'] ?? null);
        $planDays = $this->parser->parseDays($row['plan_days'] ?? null);

        if ($endDate === null && $planDays !== null) {
            $endDate = Carbon::parse($startDate)->addDays($planDays)->toDateString();
        }

        if ($endDate === null && $planDays === null) {
            $planDays = self::DEFAULT_PLAN_DAYS;
            $endDate = Carbon::parse($startDate)->addDays($planDays)->toDateString();
        }

        if ($planDays === null && $endDate !== null) {
            $planDays = max(Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)), 1);
        }

        $planDays ??= self::DEFAULT_PLAN_DAYS;
        $amount ??= 0.0;

        $plan = $this->resolvePlan($planName, $amount, $planDays);

        if ($this->subscriptionAlreadyExists($member, $plan, $startDate)) {
            return false;
        }

        $today = Carbon::today(AppConfig::timezone());
        $status = match (true) {
            Carbon::parse($startDate)->gt($today) => Status::Upcoming,
            Carbon::parse($endDate)->lt($today) => Status::Expired,
            default => Status::Ongoing,
        };

        Subscription::query()->create([
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'type' => 'official',
        ]);

        return true;
    }

    private function resolvePlan(?string $planName, float $amount, int $days): Plan
    {
        $baseName = $planName ?? __('app.settings.import.default_plan_name', ['amount' => $amount]);

        $existing = Plan::query()
            ->where('name', $baseName)
            ->where('amount', $amount)
            ->where('days', $days)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $name = $baseName;
        if (Plan::query()->where('name', $name)->exists()) {
            $name = "{$baseName} ({$amount})";
        }

        if (Plan::query()->where('name', $name)->exists()) {
            $name = "{$baseName} ({$amount}/{$days}d)";
        }

        $codeBase = Str::upper(Str::slug($name, '_'));
        $code = $codeBase;
        $suffix = 1;

        while (Plan::query()->where('code', $code)->exists()) {
            $code = "{$codeBase}_{$suffix}";
            $suffix++;
        }

        return Plan::query()->create([
            'service_id' => $this->defaultServiceId(),
            'name' => $name,
            'code' => $code,
            'description' => __('app.settings.import.imported_plan_description'),
            'days' => $days,
            'amount' => $amount,
            'status' => Status::Active,
        ]);
    }

    private function defaultServiceId(): int
    {
        $service = Service::query()->first();

        if ($service !== null) {
            return $service->id;
        }

        return Service::query()->create([
            'name' => __('app.settings.import.default_service_name'),
            'description' => __('app.settings.import.default_service_description'),
        ])->id;
    }

    private function subscriptionAlreadyExists(Member $member, Plan $plan, string $startDate): bool
    {
        return Subscription::query()
            ->where('member_id', $member->id)
            ->where('plan_id', $plan->id)
            ->whereDate('start_date', $startDate)
            ->exists();
    }
}
