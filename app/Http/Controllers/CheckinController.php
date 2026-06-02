<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\RateLimiter;

class CheckInController extends Controller
{
    private const RATE_LIMIT_MINUTES = 30;

    /**
     * Handle QR scan: validate token, find active subscription, record check-in.
     * Returns JSON for external scanners; HTML view for mobile browsers.
     */
    public function scan(Request $request, string $qrToken): JsonResponse|Response
    {
        $settings = Helpers::getSettings();

        if (! (bool) data_get($settings, 'checkin.enabled', true)) {
            return $this->respond($request, 'error', __('app.checkin.disabled'), null, 503);
        }

        $member = Member::where('checkin_token', $qrToken)->first();

        if (! $member) {
            return $this->respond($request, 'error', __('app.checkin.invalid_token'), null, 404);
        }

        // Rate limiting: 1 check-in per member per RATE_LIMIT_MINUTES
        $rateLimitKey = "checkin:{$member->id}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return $this->respond(
                $request,
                'rate_limited',
                __('app.checkin.rate_limited', ['minutes' => ceil($seconds / 60)]),
                $member,
                429,
            );
        }

        $timezone = AppConfig::timezone();
        $now = Carbon::now($timezone);

        // Find active subscription
        $subscription = Subscription::query()
            ->where('member_id', $member->id)
            ->whereDate('start_date', '<=', $now->toDateString())
            ->whereDate('end_date', '>=', $now->toDateString())
            ->whereNotIn('status', ['cancelled', 'renewed'])
            ->latest('end_date')
            ->first();

        $requireActive = (bool) data_get($settings, 'checkin.require_active_subscription', false);
        $alertOnExpired = (bool) data_get($settings, 'checkin.alert_on_expired', true);

        $status = 'success';
        $message = __('app.checkin.success', ['name' => $member->name]);

        if (! $subscription) {
            if ($requireActive) {
                return $this->respond($request, 'no_subscription', __('app.checkin.no_active_subscription'), $member, 422);
            }

            if ($alertOnExpired) {
                $status = 'warning';
                $message = __('app.checkin.no_active_subscription_warning', ['name' => $member->name]);
            }
        }

        $checkIn = CheckIn::create([
            'member_id' => $member->id,
            'subscription_id' => $subscription?->id,
            'checked_in_at' => $now,
            'method' => 'qr',
        ]);

        RateLimiter::hit($rateLimitKey, self::RATE_LIMIT_MINUTES * 60);

        return $this->respond($request, $status, $message, $member, 200, $checkIn, $subscription);
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

        $checkIn = CheckIn::query()
            ->where('member_id', $member->id)
            ->whereNull('checked_out_at')
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
     *
     * @param  array<string, mixed>|null  $member
     */
    private function respond(
        Request $request,
        string $status,
        string $message,
        ?Member $member,
        int $httpStatus = 200,
        ?CheckIn $checkIn = null,
        ?Subscription $subscription = null,
    ): JsonResponse|Response {
        $data = [
            'status' => $status,
            'message' => $message,
            'member' => $member ? ['id' => $member->id, 'name' => $member->name] : null,
            'subscription' => $subscription ? ['id' => $subscription->id, 'plan' => $subscription->plan?->name] : null,
            'checked_in_at' => $checkIn?->checked_in_at?->toIso8601String(),
        ];

        if ($request->expectsJson()) {
            return response()->json($data, $httpStatus);
        }

        return response()->view('checkin.scan', array_merge($data, [
            'member' => $member,
            'subscription' => $subscription,
            'checkIn' => $checkIn,
        ]), $httpStatus);
    }
}
