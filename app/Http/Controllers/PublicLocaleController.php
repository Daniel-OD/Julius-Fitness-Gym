<?php

namespace App\Http\Controllers;

use App\Support\PublicLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicLocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, PublicLocale::supported(), true), 404);

        $request->session()->put(PublicLocale::SESSION_KEY, $locale);

        $redirectTo = $request->headers->get('referer');

        if (! is_string($redirectTo) || $redirectTo === '') {
            return redirect()->route('home');
        }

        return redirect()->to($redirectTo);
    }
}
