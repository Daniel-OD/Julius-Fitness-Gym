<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Member;
use App\Models\Plan;
use App\Services\Members\MemberQrCodeService;
use App\Services\Members\MemberSubscriptionAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private MemberQrCodeService $qrCodeService,
        private MemberSubscriptionAccessService $subscriptionAccess,
    ) {}

    public function index(Request $request): View
    {
        $members = Member::query()
            ->with(['subscriptions.plan'])
            ->when($request->search, fn ($q, $s) => $q->where(function ($query) use ($s): void {
                $query->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%");
            }))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('members.index', compact('members'));
    }

    public function create(): View
    {
        $plans = Plan::where('status', Status::Active)->orderBy('name')->get();

        return view('members.create', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:members,email'],
            'contact' => ['nullable', 'string', 'max:50'],
            'emergency_contact' => ['nullable', 'string', 'max:50'],
            'health_issue' => ['nullable', 'string', 'max:1000'],
            'gender' => ['nullable', 'in:male,female,other'],
            'dob' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'source' => ['nullable', 'in:word_of_mouth,promotions,others'],
            'goal' => ['nullable', 'in:fitness,fatloss,weightgain,body_building,others'],
            'status' => ['required', 'in:active,inactive'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('members', 'public');
        }

        $member = Member::create($validated);

        return redirect()->route('web.members.show', $member)
            ->with('success', __('app.notifications.member_created'));
    }

    public function show(Member $member): View
    {
        $member->load(['subscriptions.plan', 'subscriptions.invoices']);

        return view('members.show', compact('member'));
    }

    public function qr(Member $member): View
    {
        $member->ensureCheckinToken();

        $access = $this->subscriptionAccess->forMember($member);
        $qrSvg = $this->qrCodeService->svgForMember($member);

        return view('members.qr', [
            'member' => $member,
            'access' => $access,
            'qrSvg' => $qrSvg,
        ]);
    }

    public function qrDownload(Member $member): Response
    {
        $member->ensureCheckinToken();

        $svg = $this->qrCodeService->svgForMember($member);
        $filename = 'member-'.$member->code.'-qr.svg';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function edit(Member $member): View
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:members,email,'.$member->id],
            'contact' => ['nullable', 'string', 'max:50'],
            'emergency_contact' => ['nullable', 'string', 'max:50'],
            'health_issue' => ['nullable', 'string', 'max:1000'],
            'gender' => ['nullable', 'in:male,female,other'],
            'dob' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'source' => ['nullable', 'in:word_of_mouth,promotions,others'],
            'goal' => ['nullable', 'in:fitness,fatloss,weightgain,body_building,others'],
            'status' => ['required', 'in:active,inactive'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $validated['photo'] = $request->file('photo')->store('members', 'public');
        }

        $member->update($validated);

        return redirect()->route('web.members.show', $member)
            ->with('success', __('app.notifications.success'));
    }

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }

        $member->delete();

        return redirect()->route('web.members.index')
            ->with('success', __('app.notifications.success'));
    }
}
