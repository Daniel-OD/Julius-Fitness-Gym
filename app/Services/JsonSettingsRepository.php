<?php

namespace App\Services;

use App\Contracts\SettingsRepository;
use App\Support\MailConfigurator;

class JsonSettingsRepository implements SettingsRepository
{
    private const string SETTINGS_PATH = 'data/settingsData.json';

    /**
     * @var array<string, mixed>|null
     */
    private ?array $cachedSettings = null;

    /**
     * @var array<string, mixed>|null
     */
    protected static ?array $testOverride = null;

    /**
     * @param  array<string, mixed>|null  $override
     */
    public function setTestOverride(?array $override): void
    {
        static::$testOverride = $override;
        $this->cachedSettings = null;
    }

    public function get(): array
    {
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }

        if (static::$testOverride !== null) {
            return $this->cachedSettings = $this->normalize(static::$testOverride);
        }

        $filePath = storage_path(self::SETTINGS_PATH);

        if (! file_exists($filePath)) {
            $this->initializeFile($filePath);
        }

        $settings = json_decode((string) file_get_contents($filePath), true) ?? [];
        $settings = is_array($settings) ? $settings : [];

        return $this->cachedSettings = $this->normalize($settings);
    }

    public function put(array $settings): void
    {
        $normalized = $this->normalize($settings);

        if (app()->runningUnitTests()) {
            static::$testOverride = $normalized;
            $this->cachedSettings = $normalized;

            return;
        }

        $filePath = storage_path(self::SETTINGS_PATH);

        if (! file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, json_encode($normalized, JSON_PRETTY_PRINT));

        $this->cachedSettings = $normalized;
    }

    private function initializeFile(string $filePath): void
    {
        if (! file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, json_encode([
            'general' => [],
            'invoice' => [],
            'member' => [],
            'charges' => [],
            'expenses' => [],
            'subscriptions' => [],
            'checkin' => [],
            'notifications' => [],
            'backup' => [],
            'mail' => [],
        ], JSON_PRETTY_PRINT));
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function normalize(array $settings): array
    {
        foreach (['general', 'invoice', 'member', 'charges', 'expenses', 'subscriptions', 'payments', 'notifications', 'backup', 'checkin', 'mail'] as $key) {
            if (! array_key_exists($key, $settings) || ! is_array($settings[$key])) {
                $settings[$key] = [];
            }
        }

        /** @var array<string, mixed> $notifications */
        $notifications = $settings['notifications'];
        if (! array_key_exists('email', $notifications) || ! is_array($notifications['email'])) {
            $notifications['email'] = [];
        }

        /** @var array<string, mixed> $emailSettings */
        $emailSettings = $notifications['email'];
        foreach ([
            'enabled' => false,
            'auto_send_invoice_issued' => false,
            'auto_send_payment_receipt' => false,
            'invoice_subject_template' => 'Invoice {invoice_number} - {status}',
            'receipt_subject_template' => 'Payment received - {invoice_number}',
        ] as $key => $default) {
            if (! array_key_exists($key, $emailSettings)) {
                $emailSettings[$key] = $default;
            }
        }

        $settings['notifications']['email'] = $emailSettings;

        /** @var array<string, mixed> $mail */
        $mail = $settings['mail'];
        foreach (MailConfigurator::defaultMailSettings() as $key => $default) {
            if (! array_key_exists($key, $mail)) {
                $mail[$key] = $default;
            }
        }

        $settings['mail'] = $mail;

        /** @var array<string, mixed> $checkin */
        $checkin = $settings['checkin'];
        foreach ([
            'enabled' => true,
            'require_active_subscription' => false,
            'auto_checkout_after_hours' => 3,
            'present_now_grace_minutes' => 15,
        ] as $key => $default) {
            if (! array_key_exists($key, $checkin)) {
                $checkin[$key] = $default;
            }
        }

        $settings['checkin'] = $checkin;

        /** @var array<string, mixed> $general */
        $general = $settings['general'];
        if (! array_key_exists('admin_guide_enabled', $general)) {
            $general['admin_guide_enabled'] = false;
        }
        $settings['general'] = $general;

        /** @var array<string, mixed> $charges */
        $charges = $settings['charges'];
        $charges['discounts'] = $this->normalizeTagList($charges['discounts'] ?? []);
        $settings['charges'] = $charges;

        /** @var array<string, mixed> $expenses */
        $expenses = $settings['expenses'];
        $expenses['categories'] = $this->normalizeTagList($expenses['categories'] ?? []);
        $settings['expenses'] = $expenses;

        return $settings;
    }

    /**
     * @return list<string>
     */
    private function normalizeTagList(mixed $value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $item): string => trim((string) $item),
            $value,
        ), fn (string $item): bool => $item !== ''));
    }
}
