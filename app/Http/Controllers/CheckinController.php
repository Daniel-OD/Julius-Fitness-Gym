<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\CheckIns\CheckInResult;
use App\Services\CheckIns\CheckInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class CheckInController extends Controller
{
    public function __construct(
        private readonly CheckInService $checkIns,
    ) {}

    /**
     * Handle QR scan: validate token, decide outcome, record check-in.
     * Returns JSON for external scanners; HTML view for mobile browsers.
     */
    public function scan(Request $request, string $qrToken): JsonResponse|Response
    {
        return $this->respond($request, $this->checkIns->recordScan($qrToken));
    }

    /**
     * Record checkout for a member's latest open check-in.
     */
    public function checkout(Request $request, string $qrToken): JsonResponse|RedirectResponse
    {
        $member = Member::where('checkin_token', $qrToken)->first();

        if (! $member) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => __('app.checkin.invalid_token')], 404);
            }

            return redirect()->back()->withErrors(['token' => __('app.checkin.invalid_token')]);
        }

        $checkIn = $this->checkIns->openSessionQuery($member->id)
            ->latest('checked_in_at')
            ->first();

        if (! $checkIn) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => __('app.checkin.no_open_checkin')], 404);
            }

            return redirect()->back()->withErrors(['checkin' => __('app.checkin.no_open_checkin')]);
        }

        $checkIn->update(['checked_out_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => __('app.checkin.checkout_success', ['name' => $member->name]),
                'duration' => $checkIn->durationMinutes(),
            ]);
        }

        return redirect()->back()->with('success', __('app.checkin.checkout_success', ['name' => $member->name]));
    }

    /**
     * Build a consistent response for both JSON (scanner) and HTML (mobile) clients.
     */
    private function respond(Request $request, CheckInResult $result): JsonResponse|Response
    {
        $member = $result->member;
        $checkIn = $result->entryRecorded() ? $result->checkIn : null;

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $result->result,
                'message' => $result->message,
                'color' => $result->color(),
                'member' => $member ? ['id' => $member->id, 'name' => $member->name] : null,
                'subscription' => $result->subscription
                    ? ['id' => $result->subscription->id, 'plan' => $result->subscription->plan?->name]
                    : null,
                'checked_in_at' => $checkIn?->checked_in_at?->toIso8601String(),
            ], $result->httpStatus);
        }

        return response()->view('checkin.scan', [
            'status' => $result->result,
            'message' => $result->message,
            'member' => $member,
            'subscription' => $result->subscription,
            'checkIn' => $checkIn,
            'qrToken' => $member?->checkin_token,
            'canCheckout' => $member && $result->result === 'already_present',
        ], $result->httpStatus);
    }
}
