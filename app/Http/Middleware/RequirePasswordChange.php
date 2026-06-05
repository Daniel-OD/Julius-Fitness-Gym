<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect authenticated users who must change their password to the force-change page.
 *
 * Applied in authMiddleware on every Filament panel request so the flag is
 * enforced even on existing sessions — not only immediately after login.
 */
class RequirePasswordChange
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->must_change_password) {
            $panelId = Filament::getCurrentPanel()?->getId();

            if (
                ! $request->routeIs('filament.*.pages.force-password-change') &&
                ! $request->routeIs("filament.{$panelId}.auth.logout")
            ) {
                return redirect()->route("filament.{$panelId}.pages.force-password-change");
            }
        }

        return $next($request);
    }
}
