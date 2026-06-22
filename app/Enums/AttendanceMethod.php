<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AttendanceMethod: string implements HasColor, HasLabel
{
    case Qr = 'qr';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return __('app.hr.attendance_methods.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Qr => 'info',
            self::Manual => 'gray',
        };
    }
}
