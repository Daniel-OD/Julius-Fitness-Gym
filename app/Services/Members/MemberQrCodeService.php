<?php

namespace App\Services\Members;

use App\Models\Member;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class MemberQrCodeService
{
    public function checkinUrl(Member $member): string
    {
        return route('checkin.scan', ['token' => $member->checkin_token]);
    }

    public function svg(string $data): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'scale' => 8,
            'imageTransparent' => false,
            'addQuietzone' => true,
        ]);

        return (new QRCode($options))->render($data);
    }

    public function svgForMember(Member $member): string
    {
        return $this->svg($this->checkinUrl($member));
    }
}
