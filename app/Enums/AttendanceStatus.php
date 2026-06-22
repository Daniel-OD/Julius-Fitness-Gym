<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasColor, HasLabel
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case HalfDay = 'half_day';

    public function getLabel(): string
    {
        return __('app.hr.attendance_statuses.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Late => 'warning',
            self::HalfDay => 'info',
            self::Absent => 'danger',
        };
    }

    public function countsAsPresent(): bool
    {
        return in_array($this, [self::Present, self::Late, self::HalfDay], true);
    }

    public function presentWeight(): float
    {
        return match ($this) {
            self::HalfDay => 0.5,
            self::Present, self::Late => 1.0,
            self::Absent => 0.0,
        };
    }
}
