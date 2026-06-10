<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('member.auth.index', [
            'mode' => 'login',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validator = validator($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->redirectTo(route('member.login'));
        }

        $credentials = $validator->validated();

        $this->ensureIsNotRateLimited($request);

        $member = Member::query()
            ->where('email', $credentials['email'])
            ->first();

        if ($member && blank($member->password) && filled($member->email)) {
            return redirect()
                ->route('member.set-password', ['email' => $member->email])
                ->with('status', __('app.member_portal.first_login_set_password'));
        }

        if (! Auth::guard('member')->attempt(
            $credentials,
            $request->boolean('remember'),
        )) {
            RateLimiter::hit($this->throttleKey($request), 60);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ])->redirectTo(route('member.login'));
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        return redirect()->intended('/member/dashboard');
    }

    public function showSetPassword(Request $request): View
    {
        return view('member.auth.set-password', [
            'email' => $request->string('email')->toString() ?: old('email'),
        ]);
    }

    public function setPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'exists:members,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $member = Member::query()
            ->where('email', $validated['email'])
            ->whereNull('password')
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'email' => __('app.member_portal.password_already_set'),
            ]);
        }

        $member->forceFill(['password' => $validated['password']])->save();

        Auth::guard('member')->login($member);

        $request->session()->regenerate();

        return redirect()->intended('/member/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('member')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/member/login');
    }

    /**
     * @throws ValidationException
     */
    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ])->redirectTo(route('member.login'));
    }

    private function throttleKey(Request $request): string
    {
        return 'member-login|'.Str::transliterate($request->ip() ?? '');
    }
}
