<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasColor, HasLabel
{
    case Booked = 'booked';
    case Attended = 'attended';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        $key = 'app.classes.booking_statuses.'.$this->value;
        $translated = __($key);

        return $translated !== $key ? $translated : $this->name;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Booked => 'info',
            self::Attended => 'success',
            self::Cancelled => 'danger',
        };
    }
}
