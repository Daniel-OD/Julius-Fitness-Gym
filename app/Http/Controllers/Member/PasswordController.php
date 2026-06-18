<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('member.auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'current_password:member'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        /** @var Member $member */
        $member = Auth::guard('member')->user();

        $member->forceFill([
            'password' => Hash::make($request->string('password')->value()),
        ])->save();

        return redirect()
            ->route('member.dashboard')
            ->with('success', __('app.member_portal.password_changed'));
    }
}
