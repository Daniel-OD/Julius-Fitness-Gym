<?php

namespace App\Enums;

enum CheckinScanStatus: string
{
    case Success = 'success';
    case Expired = 'expired';
    case Invalid = 'invalid';
}
