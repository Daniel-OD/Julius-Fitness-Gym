<?php

namespace App\Http\Controllers\Member\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Support\MemberPlanIntent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private MemberPlanIntent $planIntent) {}

    public function showRegister(Request $request): View
    {
        $planId = $request->integer('plan');

        if ($planId > 0) {
            $this->planIntent->store($planId);
        }

        return view('member.auth.index', [
            'mode' => 'register',
            'intendedPlan' => $this->planIntent->resolvePlan(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:members'],
            'contact' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->redirectTo(route('member.register'));
        }

        $validated = $validator->validated();

        $member = Member::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact' => $validated['contact'],
            'password' => $validated['password'],
        ]);

        event(new Registered($member));

        auth('member')->login($member);

        return redirect()
            ->route('member.verify-email')
            ->with('status', __('app.member.auth.verify_before_continue'));
    }
}
