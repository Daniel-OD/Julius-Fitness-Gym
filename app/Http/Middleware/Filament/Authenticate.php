<?php

namespace App\Http\Middleware\Filament;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as Middleware;

/**
 * Filament auth redirect for the admin panel.
 *
 * The admin panel omits ->login() so staff authenticate at /staff/login instead of
 * /admin/login. Filament::getLoginUrl() is therefore null — fall back to the
 * manually registered staff login route.
 */
class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl() ?? route('filament.admin.auth.login');
    }
}
