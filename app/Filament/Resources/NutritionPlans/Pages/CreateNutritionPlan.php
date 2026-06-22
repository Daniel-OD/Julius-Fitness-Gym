<?php

namespace App\Filament\Resources\NutritionPlans\Pages;

use App\Filament\Resources\NutritionPlans\NutritionPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNutritionPlan extends CreateRecord
{
    protected static string $resource = NutritionPlanResource::class;

    #[\Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['assigned_by'] = auth()->id();

        return $data;
    }
}
