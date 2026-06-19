<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reception\ScanRequest;
use App\Services\CheckIns\CheckInService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ReceptionScanController extends Controller
{
    public function __construct(
        private readonly CheckInService $checkIns,
    ) {}

    /**
     * Fullscreen webcam scanning page for the front desk.
     */
    public function index(): View
    {
        return view('reception.scan');
    }

    /**
     * Resolve a decoded QR payload and record the check-in attempt.
     */
    public function store(ScanRequest $request): JsonResponse
    {
        $result = $this->checkIns->recordScan(
            $this->extractToken($request->string('code')->trim()->value()),
        );

        return response()->json([
            'result' => $result->result,
            'color' => $result->color(),
            'message' => $result->message,
            'member' => $result->member
                ? ['name' => $result->member->name, 'code' => $result->member->code]
                : null,
            'subscription' => $result->subscription
                ? [
                    'plan' => $result->subscription->plan->name,
                    'valid_until' => $result->subscription->end_date->translatedFormat('d M Y'),
                ]
                : null,
            'checked_in_at' => $result->entryRecorded()
                ? $result->checkIn?->checked_in_at?->toIso8601String()
                : null,
        ], $result->httpStatus);
    }

    /**
     * Member QR codes encode the public check-in URL; hardware scanners may
     * send the bare token instead. Accept both.
     */
    private function extractToken(string $code): string
    {
        if (preg_match('#/checkin/([A-Za-z0-9]+)#', $code, $matches) === 1) {
            return $matches[1];
        }

        return Str::of($code)->afterLast('/')->value();
    }
}
