<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ExerciseCategory: string implements HasColor, HasLabel
{
    case Strength = 'strength';
    case Cardio = 'cardio';
    case Flexibility = 'flexibility';
    case Balance = 'balance';

    public function getLabel(): string
    {
        $key = 'app.fitness.exercise_categories.'.$this->value;
        $translated = __($key);

        return $translated !== $key ? $translated : $this->name;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Strength => 'danger',
            self::Cardio => 'info',
            self::Flexibility => 'success',
            self::Balance => 'warning',
        };
    }
}
