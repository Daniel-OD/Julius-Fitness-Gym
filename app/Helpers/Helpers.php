<?php

namespace App\Helpers;

use App\Contracts\SequenceRepository;
use App\Contracts\SettingsRepository;
use App\Models\Plan;
use App\Services\JsonSettingsRepository;
use App\Support\AppConfig;
use App\Support\Billing\Currency;
use App\Support\Billing\Discounts;
use App\Support\Billing\TaxRate;
use App\Support\Data;
use App\Support\Dates\FiscalYear;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Nnjeim\World\WorldHelper;
use TypeError;

class Helpers
{
    private const DEFAULT_CURRENCY = 'RON';

    private const DEFAULT_EXPENSE_CATEGORIES = [
        'Rent',
        'Utilities',
        'Supplies',
        'Maintenance',
        'Marketing',
        'Equipment',
        'Payroll',
        'Other',
    ];

    /**
     * @return array<string, string>
     */
    private static function fallbackCurrencies(): array
    {
        return [
            'RON' => 'Romanian Leu',
            'EUR' => 'Euro',
            'USD' => 'US Dollar',
            'GBP' => 'British Pound',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function fallbackCountries(): array
    {
        return [
            'Romania' => 'Romania',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $override
     */
    public static function setTestSettingsOverride(?array $override): void
    {
        /** @var mixed $repository */
        $repository = app(SettingsRepository::class);

        if ($repository instanceof JsonSettingsRepository) {
            $repository->setTestOverride($override);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSettings(): array
    {
        return app(SettingsRepository::class)->get();
    }

    public static function isAdminGuideEnabled(): bool
    {
        $general = self::getSettings()['general'] ?? [];

        return filter_var(is_array($general) ? ($general['admin_guide_enabled'] ?? false) : false, FILTER_VALIDATE_BOOL);
    }

    public static function appTimezone(): string
    {
        return AppConfig::timezone();
    }

    /**
     * @return array<string, string>
     */
    public static function getCountries(): array
    {
        if (app()->runningUnitTests()) {
            return [];
        }

        $response = self::worldResponse('countries');

        if (! $response->success) {
            return self::fallbackCountries();
        }

        $countries = collect($response->data)
            ->pluck('name', 'name')
            ->mapWithKeys(fn (mixed $name, mixed $key): array => [Data::string($key) => Data::string($name)])
            ->all();

        return $countries !== [] ? $countries : self::fallbackCountries();
    }

    /**
     * @return array<string, string>
     */
    public static function getStates(?string $countryName): array
    {
        if (app()->runningUnitTests() || is_null($countryName)) {
            return [];
        }

        $countryResponse = self::worldResponse('countries', ['filters' => ['name' => $countryName]]);

        if (! $countryResponse->success || empty($countryResponse->data)) {
            return [];
        }

        $countryId = collect($countryResponse->data)->pluck('id')->first();

        if (! $countryId) {
            return [];
        }

        $stateResponse = self::worldResponse('states', ['filters' => ['country_id' => $countryId]]);

        if (! $stateResponse->success) {
            return [];
        }

        return collect($stateResponse->data)
            ->pluck('name', 'name')
            ->mapWithKeys(fn (mixed $name, mixed $key): array => [Data::string($key) => Data::string($name)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function getCities(?string $stateName): array
    {
        if (app()->runningUnitTests() || is_null($stateName)) {
            return [];
        }

        $stateResponse = self::worldResponse('states', ['filters' => ['name' => $stateName]]);

        if (! $stateResponse->success || empty($stateResponse->data)) {
            return [];
        }

        $stateCode = collect($stateResponse->data)->pluck('id')->first();

        if (! $stateCode) {
            return [];
        }

        $cityResponse = self::worldResponse('cities', ['filters' => ['state_id' => $stateCode]]);

        if (! $cityResponse->success || empty($cityResponse->data)) {
            return [];
        }

        return collect($cityResponse->data)
            ->pluck('name', 'name')
            ->mapWithKeys(fn (mixed $name, mixed $key): array => [Data::string($key) => Data::string($name)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function getCurrencies(): array
    {
        if (app()->runningUnitTests()) {
            return [];
        }

        $currencyResponse = self::worldResponse('currencies', ['fields' => 'name,code']);

        if (! $currencyResponse->success) {
            return self::fallbackCurrencies();
        }

        $currencies = collect($currencyResponse->data)
            ->pluck('name', 'code')
            ->mapWithKeys(fn (mixed $name, mixed $key): array => [Data::string($key) => Data::string($name)])
            ->all();

        return $currencies !== [] ? $currencies : self::fallbackCurrencies();
    }

    public static function getCurrencyCode(): string
    {
        return Currency::codeFromSettings(self::getSettings(), self::DEFAULT_CURRENCY);
    }

    public static function getSubscriptionExpiringDays(): int
    {
        $settings = self::getSettings();
        $subscriptions = is_array($settings['subscriptions'] ?? null) ? $settings['subscriptions'] : [];
        $days = $subscriptions['expiring_days'] ?? 7;

        return is_numeric($days) ? max(1, (int) $days) : 7;
    }

    /**
     * @return array<int, string>
     */
    public static function getExpenseCategories(): array
    {
        $settings = self::getSettings();
        $expenses = is_array($settings['expenses'] ?? null) ? $settings['expenses'] : [];
        $categories = $expenses['categories'] ?? null;

        if (! is_array($categories) || empty($categories)) {
            return self::DEFAULT_EXPENSE_CATEGORIES;
        }

        $normalized = [];
        foreach ($categories as $category) {
            $category = trim(Data::string($category));
            if ($category !== '') {
                $normalized[] = $category;
            }
        }

        return $normalized ?: self::DEFAULT_EXPENSE_CATEGORIES;
    }

    /**
     * @return array<string, string>
     */
    public static function getExpenseCategoryOptions(): array
    {
        $options = [];
        foreach (self::getExpenseCategories() as $category) {
            $key = Str::slug($category);
            if ($key !== '') {
                $options[$key] = $category;
            }
        }

        return $options;
    }

    public static function getExpenseCategoryLabel(?string $key): ?string
    {
        if (blank($key)) {
            return null;
        }

        return self::getExpenseCategoryOptions()[$key] ?? $key;
    }

    /**
     * @return array<string, string>
     */
    public static function getDiscounts(): array
    {
        return Discounts::optionsFromSettings(self::getSettings());
    }

    public static function getDiscountAmount(?float $discount, ?float $fee): float
    {
        return Discounts::amount($discount, $fee);
    }

    public static function getTaxRate(): float
    {
        return TaxRate::fromSettings(self::getSettings());
    }

    public static function formatCurrency(?float $value, ?string $currency = null): string
    {
        $currency = $currency ?? self::getCurrencyCode();

        return Currency::format($value, $currency);
    }

    public static function getCurrencySymbol(): string
    {
        return Currency::symbol(self::getCurrencyCode());
    }

    public static function parseDate(?string $dateString): Carbon
    {
        return $dateString ? Carbon::parse($dateString) : Carbon::now();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function getFiscalSpan(Carbon $date): array
    {
        $generalSettings = self::getSettings()['general'] ?? [];

        return FiscalYear::spanForDate($date, is_array($generalSettings) ? $generalSettings : []);
    }

    /**
     * @param  class-string  $modelClass
     */
    public static function generateLastNumber(string $type, string $modelClass, ?string $dateString = null, ?string $modalColumn = 'number'): string
    {
        return app(SequenceRepository::class)->generate($type, $modelClass, $dateString, $modalColumn);
    }

    public static function updateLastNumber(string $type, string $newNumber, ?string $date = null): void
    {
        app(SequenceRepository::class)->update($type, $newNumber, $date);
    }

    public static function calculateSubscriptionEndDate(?string $startDate, ?int $planId): string
    {
        if (! $startDate || ! $planId) {
            return '';
        }

        $plan = Plan::find($planId);
        if (! $plan || ! $plan->days) {
            return '';
        }

        return Carbon::parse($startDate)->addDays((int) $plan->days)->toDateString();
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return object{success: bool, data: array<int, array<string, mixed>>}
     */
    private static function worldResponse(string $method, array $parameters = []): object
    {
        try {
            $action = app(WorldHelper::class)->__call($method, [$parameters]);
        } catch (TypeError) {
            // nnjeim/world caches Collections forever; a stale DB cache entry can
            // deserialize as __PHP_Incomplete_Class and crash Settings country/currency selects.
            self::clearWorldCache();
            $action = app(WorldHelper::class)->__call($method, [$parameters]);
        }

        $data = $action->data ?? collect();

        if ($data instanceof Collection) {
            $data = $data->all();
        } elseif (! is_array($data)) {
            $data = [];
        }

        return (object) [
            'success' => (bool) ($action->success ?? false),
            'data' => $data,
        ];
    }

    /**
     * Drop cached nnjeim/world index results (used after cache corruption recovery).
     */
    private static function clearWorldCache(): void
    {
        if (config('cache.default') === 'database') {
            DB::table('cache')
                ->where(function ($query): void {
                    foreach (['currencies', 'countries', 'states', 'cities', 'languages', 'timezones'] as $tag) {
                        $query->orWhere('key', 'like', '%'.$tag.'%');
                    }
                })
                ->delete();

            return;
        }

        Cache::flush();
    }
}
