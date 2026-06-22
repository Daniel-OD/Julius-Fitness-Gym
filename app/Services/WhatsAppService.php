<?php

namespace App\Services;

use App\Contracts\SettingsRepository;
use App\Contracts\WhatsAppProviderInterface;
use App\Helpers\Helpers;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Subscription;
use App\Services\WhatsApp\Providers\MetaProvider;
use App\Services\WhatsApp\Providers\TwilioProvider;
use App\Services\WhatsApp\Providers\VonageProvider;
use App\Support\AppConfig;
use App\Support\Data;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp messaging service. Delegates to provider implementations.
 */
final readonly class WhatsAppService
{
    public const string PROVIDER_META = 'meta';

    public const string PROVIDER_TWILIO = 'twilio';

    public const string PROVIDER_VONAGE = 'vonage';

    public function __construct(private SettingsRepository $settingsRepository) {}

    public function isEnabled(): bool
    {
        return (bool) data_get($this->settingsRepository->get(), 'notifications.whatsapp.enabled', false);
    }

    /**
     * Send a WhatsApp template message.
     *
     * @param  array<int, string>  $variables  Ordered template body parameters.
     */
    public function sendMessage(string $phone, string $template, array $variables): bool
    {
        if (! $this->isEnabled()) {
            Log::info('Skipping WhatsApp message: disabled in settings.', [
                'template' => $template,
            ]);

            return false;
        }

        $normalizedPhone = $this->normalizePhone($phone);

        if ($normalizedPhone === null) {
            Log::info('Skipping WhatsApp message: invalid or missing phone.', [
                'template' => $template,
            ]);

            return false;
        }

        $settings = $this->settingsRepository->get();

        return $this->resolveProvider($settings)->send($normalizedPhone, $template, $variables, $settings);
    }

    public function sendSubscriptionExpiry(Member $member, Subscription $subscription, int $daysLeft): bool
    {
        $subscription->loadMissing('plan');

        $settings = $this->settingsRepository->get();
        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name')));
        $template = Data::string(data_get($settings, 'notifications.whatsapp.templates.subscription_expiry'));

        if ($template === '') {
            Log::info('Skipping WhatsApp subscription expiry: template not configured.');

            return false;
        }

        return $this->sendMessage(
            phone: Data::string($member->contact),
            template: $template,
            variables: [
                Data::string($member->name),
                Data::string($subscription->plan?->name),
                (string) max(0, $daysLeft),
                $subscription->end_date->translatedFormat('d M Y'),
                $gymName,
            ],
        );
    }

    public function sendPaymentConfirmation(Member $member, Invoice $invoice): bool
    {
        $invoice->loadMissing('subscription.plan');

        $settings = $this->settingsRepository->get();
        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name')));
        $template = Data::string(data_get($settings, 'notifications.whatsapp.templates.payment_confirmation'));

        if ($template === '') {
            Log::info('Skipping WhatsApp payment confirmation: template not configured.');

            return false;
        }

        return $this->sendMessage(
            phone: Data::string($member->contact),
            template: $template,
            variables: [
                Data::string($member->name),
                Data::string($invoice->number),
                Helpers::formatCurrency($invoice->paid_amount),
                $gymName,
            ],
        );
    }

    public function sendWelcome(Member $member): bool
    {
        $settings = $this->settingsRepository->get();
        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name')));
        $template = Data::string(data_get($settings, 'notifications.whatsapp.templates.welcome'));

        if ($template === '') {
            Log::info('Skipping WhatsApp welcome message: template not configured.');

            return false;
        }

        return $this->sendMessage(
            phone: Data::string($member->contact),
            template: $template,
            variables: [
                Data::string($member->name),
                $gymName,
            ],
        );
    }

    public function sendBirthday(Member $member): bool
    {
        $settings = $this->settingsRepository->get();
        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name')));
        $template = Data::string(data_get($settings, 'notifications.whatsapp.templates.birthday'));

        if ($template === '') {
            Log::info('Skipping WhatsApp birthday message: template not configured.');

            return false;
        }

        return $this->sendMessage(
            phone: Data::string($member->contact),
            template: $template,
            variables: [
                Data::string($member->name),
                $gymName,
            ],
        );
    }

    /** @param  array<string, mixed>  $settings */
    private function resolveProvider(array $settings): WhatsAppProviderInterface
    {
        $provider = Data::string(data_get($settings, 'notifications.whatsapp.provider', self::PROVIDER_META));

        return match ($provider) {
            self::PROVIDER_TWILIO => new TwilioProvider,
            self::PROVIDER_VONAGE => new VonageProvider,
            default => new MetaProvider,
        };
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', Data::string($phone));

        if ($digits === null || strlen($digits) < 8) {
            return null;
        }

        return $digits;
    }
}
