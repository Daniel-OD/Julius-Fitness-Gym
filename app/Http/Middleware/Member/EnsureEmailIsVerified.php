<?php

namespace App\Http\Middleware\Member;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('member')->user()?->hasVerifiedEmail()) {
            return redirect()->route('member.verify-email');
        }

        return $next($request);
    }
}
