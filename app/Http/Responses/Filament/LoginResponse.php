<?php

namespace App\Http\Responses\Filament;

use App\Models\User;
use App\Support\FilamentSession;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Filament::auth()->user();

        if ($user instanceof User) {
            $panelId = $user->postLoginPanelId(Filament::getCurrentPanel()?->getId());
            FilamentSession::lockToPanel($panelId);

            return redirect()->intended(Filament::getPanel($panelId)->getUrl());
        }

        return redirect()->intended(Filament::getUrl());
    }
}
