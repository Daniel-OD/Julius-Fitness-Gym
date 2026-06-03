<?php

namespace App\Filament\Livewire;

use Filament\Enums\ThemeMode;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Renders a light/dark toggle button in the panel topbar.
 *
 * Delegates to Filament's built-in theme switching via Alpine.js
 * $store('theme') — no server round-trip needed.
 */
class ThemeSwitcher extends Component
{
    public function render(): View
    {
        return view('filament.components.theme-switcher', [
            'default' => ThemeMode::Dark,
        ]);
    }
}
