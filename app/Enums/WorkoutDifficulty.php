<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum WorkoutDifficulty: string implements HasLabel
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    public function getLabel(): string
    {
        $key = 'app.fitness.difficulties.'.$this->value;
        $translated = __($key);

        return $translated !== $key ? $translated : $this->name;
    }
}
