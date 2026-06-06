<?php

namespace App\Filament\Livewire;

use App\Contracts\SettingsRepository;
use App\Helpers\Helpers;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AdminGuideToggle extends Component
{
    public bool $enabled = false;

    public function mount(): void
    {
        $this->enabled = Helpers::isAdminGuideEnabled();
    }

    public function toggle(): mixed
    {
        /** @var SettingsRepository $repository */
        $repository = app(SettingsRepository::class);

        $settings = $repository->get();
        $this->enabled = ! $this->enabled;
        data_set($settings, 'general.admin_guide_enabled', $this->enabled);
        /** @var array<string, mixed> $settings */
        $repository->put($settings);

        Notification::make()
            ->title($this->enabled ? __('admin_guide.enabled') : __('admin_guide.disabled'))
            ->success()
            ->send();

        $referer = request()->headers->get('referer');
        $redirectTo = is_string($referer) && $referer !== '' ? $referer : url('/');

        return redirect()->to($redirectTo);
    }

    public function render(): View
    {
        return view('filament.components.admin-guide-toggle');
    }
}
