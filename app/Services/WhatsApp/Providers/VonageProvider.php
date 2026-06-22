<?php

namespace App\Services\WhatsApp\Providers;

use App\Contracts\WhatsAppProviderInterface;
use App\Support\Data;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class VonageProvider implements WhatsAppProviderInterface
{
    private const string API_URL = 'https://api.nexmo.com/v1/messages';

    /**
     * @param  array<int, string>  $variables
     * @param  array<string, mixed>  $settings
     */
    public function send(string $phone, string $template, array $variables, array $settings): bool
    {
        $apiKey = Data::string(data_get($settings, 'notifications.whatsapp.api_key'));
        $apiSecret = Data::string(data_get($settings, 'notifications.whatsapp.api_secret'));
        $fromNumber = Data::string(data_get($settings, 'notifications.whatsapp.phone_number_id'));

        if ($apiKey === '' || $apiSecret === '' || $fromNumber === '') {
            Log::warning('Skipping WhatsApp message: Vonage credentials missing.', [
                'template' => $template,
            ]);

            return false;
        }

        $parameters = collect($variables)
            ->values()
            ->map(fn (string $value): array => ['type' => 'text', 'text' => $value])
            ->all();

        $response = Http::withBasicAuth($apiKey, $apiSecret)
            ->acceptJson()
            ->retry(3, 100)
            ->post(self::API_URL, [
                'message_type' => 'template',
                'channel' => 'whatsapp',
                'from' => $fromNumber,
                'to' => $phone,
                'whatsapp' => [
                    'policy' => 'deterministic',
                    'locale' => 'en',
                    'template' => [
                        'name' => $template,
                        'parameters' => $parameters,
                    ],
                ],
            ]);

        if ($response->successful()) {
            return true;
        }

        Log::warning('WhatsApp Vonage API request failed.', [
            'template' => $template,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        return false;
    }
}
