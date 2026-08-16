<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        // Admins/owners are legitimately authorized on both the admin and office
        // panels (see User::isAdministrator()) — re-lock to the panel they just
        // navigated to instead of tearing down the session, otherwise switching
        // panels in the same browser session logs them out entirely.
        $user = Filament::auth()->user();

        if ($user instanceof User && $user->isAdministrator() && $user->canAccessPanel($panel)) {
            FilamentSession::lockToPanel($currentPanelId);

            return $next($request);
        }

        Filament::auth()->logout();
        FilamentSession::forget();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $loginUrl = $panel->getLoginUrl() ?? match ($panel->getId()) {
            'admin' => route('filament.admin.auth.login'),
            default => '/',
        };

        return redirect()->to($loginUrl);
    }
}
