<?php

namespace App\Http\Middleware\Filament;

use Filament\Facades\Filament;
use Filament\Http\Middleware\AuthenticateSession as Middleware;

/**
 * Session re-auth redirect for Filament panels.
 *
 * The admin panel omits ->login(), so Filament::getLoginUrl() is null — fall back
 * to the staff login route, matching app/Http/Middleware/Filament/Authenticate.
 */
class AuthenticateSession extends Middleware
{
    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl() ?? route('filament.admin.auth.login');
    }
}
