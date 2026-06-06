<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $dashboard): Response
    {
        $user = $request->user();

        if ($user === null || ! in_array($dashboard, $user->accessibleDashboards(), true)) {
            if ($user !== null && $user->accessibleDashboards() !== []) {
                return redirect($user->defaultDashboardUrl());
            }

            abort(403);
        }

        return $next($request);
    }
}
