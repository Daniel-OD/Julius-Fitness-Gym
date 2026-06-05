<?php

namespace App\Http\Middleware;

use App\Support\FilamentSession;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the browser session is only used on the panel it was opened from.
 */
class EnforceFilamentPanelSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentPanel();

        if ($panel === null || ! Filament::auth()->check()) {
            return $next($request);
        }

        $lockedPanelId = FilamentSession::authenticatedPanelId();
        $currentPanelId = $panel->getId();

        if ($lockedPanelId === null) {
            FilamentSession::lockToPanel($currentPanelId);

            return $next($request);
        }

        if ($lockedPanelId === $currentPanelId) {
            return $next($request);
        }

        Filament::auth()->logout();
        FilamentSession::forget();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($panel->getLoginUrl());
    }
}
