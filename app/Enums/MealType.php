<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MealType: string implements HasLabel
{
    case Breakfast = 'breakfast';
    case Lunch = 'lunch';
    case Dinner = 'dinner';
    case Snack = 'snack';

    public function getLabel(): string
    {
        $key = 'app.fitness.meal_types.'.$this->value;
        $translated = __($key);

        return $translated !== $key ? $translated : $this->name;
    }
}
