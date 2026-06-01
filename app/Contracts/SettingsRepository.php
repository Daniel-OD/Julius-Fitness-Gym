<?php

namespace App\Contracts;

interface SettingsRepository
{
    /**
     * @return array<string, mixed>
     */
    public function get(): array;

    /**
     * @param  array<string, mixed>  $settings
     */
    public function put(array $settings): void;
}
