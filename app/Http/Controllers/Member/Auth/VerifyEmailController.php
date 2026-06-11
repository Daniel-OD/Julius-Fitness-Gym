<?php

namespace App\Http\Controllers\Member\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\Members\MemberPlanSelectionService;
use App\Support\MemberPlanIntent;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function __construct(
        private MemberPlanIntent $planIntent,
        private MemberPlanSelectionService $planSelection,
    ) {}

    public function showVerifyEmail(): View
    {
        return view('member.auth.verify-email', [
            'intendedPlan' => $this->planIntent->resolvePlan(),
        ]);
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

        $subscription = $this->planSelection->fulfillPending($member);

        if ($subscription !== null) {
            return redirect()
                ->route('member.dashboard')
                ->with('success', __('app.member.plans.selected'));
        }

        return redirect()->route('member.dashboard');
    }

    public function resend(Request $request): RedirectResponse
    {
        $request->user('member')->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
