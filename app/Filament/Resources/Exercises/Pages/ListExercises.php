<?php

namespace App\Filament\Resources\Exercises\Pages;

use App\Filament\Resources\Exercises\ExerciseResource;
use Filament\Resources\Pages\ListRecords;

class ListExercises extends ListRecords
{
    protected static string $resource = ExerciseResource::class;

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [__('app.navigation.groups.fitness'), ExerciseResource::getNavigationLabel()];
    }
}
