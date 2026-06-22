<?php

namespace App\Contracts;

interface WhatsAppProviderInterface
{
    /**
     * Send a WhatsApp template message via this provider.
     *
     * @param  array<int, string>  $variables  Ordered template body parameters.
     * @param  array<string, mixed>  $settings  Full application settings array.
     */
    public function send(string $phone, string $template, array $variables, array $settings): bool;
}
