<?php

declare(strict_types=1);

use App\Http\Middleware\AppendStudioSignature;
use App\Http\Middleware\EnsureDashboardAccess;
use App\Http\Middleware\EnsureMemberIsAuthenticated;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\Member\EnsureEmailIsVerified;
use App\Http\Middleware\SetAppLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\Exceptions\InvalidIncludeQuery;
use Spatie\QueryBuilder\Exceptions\InvalidQuery;
use Spatie\QueryBuilder\Exceptions\InvalidSortQuery;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'dashboard.access' => EnsureDashboardAccess::class,
            'member.auth' => EnsureMemberIsAuthenticated::class,
            'member.verified' => EnsureEmailIsVerified::class,
        ]);

        // Render (and similar) terminate TLS at the edge — required for signed Livewire upload URLs.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            SetAppLocale::class,
        ]);

        $middleware->api(prepend: [
            SetAppLocale::class,
            ForceJsonResponse::class,
        ]);

        $middleware->append([
            AppendStudioSignature::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->is('member', 'member/*')) {
                return route('member.login');
            }

            return route('filament.admin.auth.login');
        });

        $middleware->redirectUsersTo(function (Request $request): string {
            if (Auth::guard('member')->check()) {
                return '/member/dashboard';
            }

            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidQuery $exception, Request $request) {
            $errors = ['query' => [$exception->getMessage()]];

            if ($exception instanceof InvalidFilterQuery) {
                $errors = ['filter' => [$exception->getMessage()]];
            } elseif ($exception instanceof InvalidIncludeQuery) {
                $errors = ['include' => [$exception->getMessage()]];
            } elseif ($exception instanceof InvalidSortQuery) {
                $errors = ['sort' => [$exception->getMessage()]];
            }

            return response()->json([
                'message' => __('app.api.invalid_query'),
                'errors' => $errors,
            ], 400);
        });
    })->create();
