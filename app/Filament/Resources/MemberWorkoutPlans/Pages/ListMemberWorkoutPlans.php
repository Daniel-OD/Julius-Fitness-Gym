<?php

namespace App\Filament\Resources\MemberWorkoutPlans\Pages;

use App\Filament\Resources\MemberWorkoutPlans\MemberWorkoutPlanResource;
use App\Support\Fitness\InstructorAccess;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMemberWorkoutPlans extends ListRecords
{
    protected static string $resource = MemberWorkoutPlanResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->url(MemberWorkoutPlanResource::getUrl('create')),
        ];
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
