<?php

namespace App\Services\Members;

use App\Models\Member;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class MemberQrCodeService
{
    public function checkinUrl(Member $member): string
    {
        return route('checkin.scan', ['qrToken' => $member->checkin_token]);
    }

    public function svg(string $data): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'scale' => 8,
            'imageTransparent' => false,
            'addQuietzone' => true,
        ]);

        $output = new QRCode($options)->render($data);

        return $this->normalizeSvgOutput($output);
    }

    private function normalizeSvgOutput(string $output): string
    {
        if (! str_starts_with($output, 'data:image/svg+xml')) {
            return $output;
        }

        if (preg_match('/base64,(.+)$/s', $output, $matches) === 1) {
            return (string) base64_decode($matches[1], true);
        }

        if (preg_match('/charset=utf-8,(.+)$/s', $output, $matches) === 1) {
            return rawurldecode($matches[1]);
        }

        return $output;
    }

    public function svgForMember(Member $member): string
    {
        return $this->svg($this->checkinUrl($member));
    }
}
