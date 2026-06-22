<?php

namespace App\Services\WhatsApp\Providers;

use App\Contracts\WhatsAppProviderInterface;
use App\Support\Data;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class MetaProvider implements WhatsAppProviderInterface
{
    private const string API_VERSION = 'v21.0';

    /**
     * @param  array<int, string>  $variables
     * @param  array<string, mixed>  $settings
     */
    public function send(string $phone, string $template, array $variables, array $settings): bool
    {
        $apiKey = Data::string(data_get($settings, 'notifications.whatsapp.api_key'));
        $phoneNumberId = Data::string(data_get($settings, 'notifications.whatsapp.phone_number_id'));

        if ($apiKey === '' || $phoneNumberId === '') {
            Log::warning('Skipping WhatsApp message: Meta credentials missing.', [
                'template' => $template,
            ]);

            return false;
        }

        $locale = $this->resolveLocale($settings);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->retry(3, 100)
            ->post(
                'https://graph.facebook.com/'.self::API_VERSION."/{$phoneNumberId}/messages",
                [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'template',
                    'template' => [
                        'name' => $template,
                        'language' => ['code' => $locale],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => array_map(
                                    fn (string $value): array => ['type' => 'text', 'text' => $value],
                                    $variables,
                                ),
                            ],
                        ],
                    ],
                ],
            );

        if ($response->successful()) {
            return true;
        }

        Log::warning('WhatsApp Meta API request failed.', [
            'template' => $template,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        return false;
    }

    /** @param  array<string, mixed>  $settings */
    private function resolveLocale(array $settings): string
    {
        $locale = Data::string(data_get($settings, 'general.locale', 'en'));

        return match ($locale) {
            'ro' => 'ro',
            default => 'en',
        };
    }
}
