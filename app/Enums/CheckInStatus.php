<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CheckInStatus: string implements HasColor, HasLabel
{
    case Success = 'success';
    case GraceEntry = 'grace_entry';
    case Blocked = 'blocked';

    public function getLabel(): string
    {
        return __('app.checkins.statuses.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Success => 'success',
            self::GraceEntry => 'warning',
            self::Blocked => 'danger',
        };
    }
}
