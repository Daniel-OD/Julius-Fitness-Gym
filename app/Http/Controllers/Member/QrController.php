<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\Members\MemberQrCodeService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class QrController extends Controller
{
    public function show(MemberQrCodeService $qrCodeService): View
    {
        /** @var Member $member */
        $member = auth('member')->user();
        $member->ensureCheckinToken();

        $qrSvg = $qrCodeService->svgForMember($member);

        return view('member.qr.show', compact('member', 'qrSvg'));
    }

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
