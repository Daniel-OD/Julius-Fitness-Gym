<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LeaveType: string implements HasLabel
{
    case Annual = 'annual';
    case Sick = 'sick';
    case Unpaid = 'unpaid';
    case Other = 'other';

    public function getLabel(): string
    {
        return __('app.hr.leave_types.'.$this->value);
    }

    public function isPaid(): bool
    {
        return $this !== self::Unpaid;
    }
}
