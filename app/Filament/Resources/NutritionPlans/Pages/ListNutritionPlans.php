<?php

namespace App\Filament\Resources\NutritionPlans\Pages;

use App\Filament\Resources\NutritionPlans\NutritionPlanResource;
use App\Support\Fitness\InstructorAccess;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNutritionPlans extends ListRecords
{
    protected static string $resource = NutritionPlanResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->url(NutritionPlanResource::getUrl('create'))];
    }

    #[\Override]
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $user = auth()->user();
        $ids = $user ? InstructorAccess::assignedMemberIds($user) : null;
        if (is_array($ids)) {
            $query->whereIn('member_id', $ids);
        }

        return $query;
    }
}
