<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\Auth\PasswordResetService;
use App\Services\Email\PasswordResetEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private PasswordResetService $passwordResetService,
        private PasswordResetEmailService $passwordResetEmailService,
    ) {}

    public function create(): View
    {
        return view('member.auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $member = Member::query()
            ->where('email', $validated['email'])
            ->first();

        if ($member instanceof Member && filled($member->email)) {
            $plainPassword = $this->passwordResetService->resetMemberPassword($member);
            $this->passwordResetEmailService->queueMemberPasswordReset($member->id, $plainPassword);
        }

        return back()->with('status', __('app.member_portal.password_reset_sent'));
    }
}
