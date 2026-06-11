<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\Members\MemberQrCodeService;
use Illuminate\Http\Response;

class QrController extends Controller
{
    public function download(MemberQrCodeService $qrCodeService): Response
    {
        /** @var Member $member */
        $member = auth('member')->user();
        $member->ensureCheckinToken();

        $svg = $qrCodeService->svgForMember($member);
        $filename = 'member-'.$member->code.'-qr.svg';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
