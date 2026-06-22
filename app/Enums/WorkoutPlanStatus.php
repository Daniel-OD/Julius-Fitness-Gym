<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WorkoutPlanStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        $key = 'app.fitness.plan_statuses.'.$this->value;
        $translated = __($key);

        return $translated !== $key ? $translated : $this->name;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Completed => 'gray',
            self::Cancelled => 'danger',
        };
    }
}
