<?php

namespace App\Http\Controllers\Member\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function show(): View
    {
        return view('member.auth.verify-email');
    }

    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $member = Member::findOrFail($id);

        abort_unless((string) auth('member')->id() === $id, 403);
        abort_if(! hash_equals($hash, sha1($member->getEmailForVerification())), 403);
        abort_unless($request->hasValidSignature(), 403);

        if (! $member->hasVerifiedEmail()) {
            $member->markEmailAsVerified();
            event(new Verified($member));
        }

        return redirect()->route('member.plans');
    }

    public function resend(Request $request): RedirectResponse
    {
        $request->user('member')->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
