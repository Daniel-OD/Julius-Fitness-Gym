<?php

namespace App\Http\Middleware;

use App\Support\AppLocale;
use App\Support\PublicLocale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAppLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (PublicLocale::shouldApply($request)) {
            PublicLocale::apply($request);
        } else {
            AppLocale::apply($request);
        }

        return $next($request);
    }
}
