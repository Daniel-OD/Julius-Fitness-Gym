<?php

namespace App\Filament\Resources\WorkoutTemplates\Pages;

use App\Filament\Resources\WorkoutTemplates\WorkoutTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkoutTemplate extends CreateRecord
{
    protected static string $resource = WorkoutTemplateResource::class;

    #[\Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
