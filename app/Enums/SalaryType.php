<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SalaryType: string implements HasLabel
{
    case Monthly = 'monthly';
    case Hourly = 'hourly';

    public function getLabel(): string
    {
        return __('app.hr.salary_types.'.$this->value);
    }
}
