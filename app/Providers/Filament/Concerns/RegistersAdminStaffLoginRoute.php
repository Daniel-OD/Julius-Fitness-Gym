<?php

namespace App\Providers\Filament\Concerns;

use App\Filament\Auth\Login;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

/**
 * Register the admin staff login page at /staff/login.
 *
 * Filament's loginRouteSlug() only applies under the panel path (/admin/...),
 * so the staff login URL is registered manually and named filament.admin.auth.login.
 */
trait RegistersAdminStaffLoginRoute
{
    public const STAFF_LOGIN_PATH = 'staff/login';

    protected function registerAdminStaffLoginRoute(): void
    {
        Route::middleware(Filament::getPanel('admin')->getMiddleware())
            ->group(function (): void {
                Route::get(self::STAFF_LOGIN_PATH, Login::class)
                    ->name('filament.admin.auth.login');
            });
    }
}
