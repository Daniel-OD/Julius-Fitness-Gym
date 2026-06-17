<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\Members\MemberQrCodeService;
use App\Services\Members\MemberSubscriptionAccessService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private MemberQrCodeService $qrCodeService,
        private MemberSubscriptionAccessService $subscriptionAccess,
    ) {}

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
}
