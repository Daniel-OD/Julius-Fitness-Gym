<?php

namespace App\Services\WhatsApp\Providers;

use App\Contracts\WhatsAppProviderInterface;
use App\Support\Data;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class TwilioProvider implements WhatsAppProviderInterface
{
    /**
     * @param  array<int, string>  $variables
     * @param  array<string, mixed>  $settings
     */
    public function send(string $phone, string $template, array $variables, array $settings): bool
    {
        $accountSid = Data::string(data_get($settings, 'notifications.whatsapp.account_sid'));
        $authToken = Data::string(data_get($settings, 'notifications.whatsapp.api_key'));
        $fromNumber = Data::string(data_get($settings, 'notifications.whatsapp.phone_number_id'));

        if ($accountSid === '' || $authToken === '' || $fromNumber === '') {
            Log::warning('Skipping WhatsApp message: Twilio credentials missing.', [
                'template' => $template,
            ]);

            return false;
        }

        $payload = [
            'From' => $this->toWhatsAppAddress($fromNumber),
            'To' => $this->toWhatsAppAddress($phone),
        ];

        if (str_starts_with($template, 'HX')) {
            $payload['ContentSid'] = $template;
            $payload['ContentVariables'] = json_encode(
                collect($variables)
                    ->values()
                    ->mapWithKeys(fn (string $value, int $index): array => [(string) ($index + 1) => $value])
                    ->all(),
                JSON_THROW_ON_ERROR,
            );
        } else {
            $payload['Body'] = $this->renderBody($template, $variables);
        }

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->retry(3, 100)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", $payload);

        if ($response->successful()) {
            return true;
        }

        Log::warning('WhatsApp Twilio API request failed.', [
            'template' => $template,
            'status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);

        return false;
    }

    /** @param  array<int, string>  $variables */
    private function renderBody(string $template, array $variables): string
    {
        $body = $template;

        foreach ($variables as $index => $value) {
            $body = str_replace('{{'.($index + 1).'}}', $value, $body);
        }

        return $body;
    }

    private function toWhatsAppAddress(string $phone): string
    {
        $normalized = ltrim(trim($phone), '+');

        if (str_starts_with(strtolower($normalized), 'whatsapp:')) {
            return $normalized;
        }

        return 'whatsapp:+'.$normalized;
    }
}
