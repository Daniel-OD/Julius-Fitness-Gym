<?php

namespace App\Filament\Auth;

use App\Models\User;
use App\Support\FilamentSession;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;

class Login extends BaseLogin
{
    public const RELAXED_PANEL_ACCESS_ATTRIBUTE = 'filament.relaxed_panel_access';

    #[\Override]
    public function mount(): void
    {
        $currentPanelId = Filament::getCurrentPanel()?->getId();

        if (Filament::auth()->check()) {
            $lockedPanelId = FilamentSession::authenticatedPanelId();

            if ($lockedPanelId === null && $currentPanelId !== null) {
                FilamentSession::lockToPanel($currentPanelId);
                $lockedPanelId = $currentPanelId;
            }

            if ($lockedPanelId === $currentPanelId) {
                redirect()->intended($this->intendedUrlFor(Filament::auth()->user()));

                // Stop here — the session is valid for this panel and must be
                // preserved. Falling through would invalidate it via logout.
                return;
            }

            $this->logoutPreviousSession();
        }

        $this->form->fill();
    }

    protected function logoutPreviousSession(): void
    {
        Filament::auth()->logout();
        FilamentSession::forget();
        session()->invalidate();
        session()->regenerateToken();
    }

    #[\Override]
    public function authenticate(): ?LoginResponse
    {
        request()->attributes->set(self::RELAXED_PANEL_ACCESS_ATTRIBUTE, true);

        try {
            return parent::authenticate();
        } finally {
            request()->attributes->remove(self::RELAXED_PANEL_ACCESS_ATTRIBUTE);
        }
    }

    public function intendedUrlFor(?Authenticatable $user): string
    {
        if ($user instanceof User) {
            return $user->postLoginUrl(Filament::getCurrentPanel()?->getId());
        }

        return Filament::getUrl();
    }
}
