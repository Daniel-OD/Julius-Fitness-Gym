<?php

namespace App\Http\Controllers;

use App\Services\Hr\AttendanceService;
use App\Services\Hr\StaffAttendanceResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StaffCheckinController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendance,
    ) {}

    public function scan(Request $request, string $token): JsonResponse|Response
    {
        return $this->respond($request, $this->attendance->recordScan($token));
    }

    public function checkout(Request $request, string $token): JsonResponse|Response
    {
        return $this->respond($request, $this->attendance->recordCheckout($token));
    }

    private function respond(Request $request, StaffAttendanceResult $result): JsonResponse|Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => $result->result,
                'message' => $result->message,
                'color' => $result->color(),
                'user' => $result->user ? ['id' => $result->user->id, 'name' => $result->user->name] : null,
            ], $result->httpStatus);
        }

        return response()->view('staff.checkin', [
            'status' => $result->result,
            'message' => $result->message,
            'user' => $result->user,
            'profile' => $result->profile,
            'token' => $result->profile?->attendance_token,
            'canCheckout' => $result->result === 'already_checked_in',
        ], $result->httpStatus);
    }
}
