<?php

namespace App\Filament\Resources\MemberWorkoutPlans\Pages;

use App\Filament\Resources\MemberWorkoutPlans\MemberWorkoutPlanResource;
use App\Models\Member;
use App\Services\Fitness\WorkoutPlanAssignmentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMemberWorkoutPlan extends CreateRecord
{
    protected static string $resource = MemberWorkoutPlanResource::class;

    #[\Override]
    protected function handleRecordCreation(array $data): Model
    {
        $member = Member::query()->findOrFail((int) $data['member_id']);

        return app(WorkoutPlanAssignmentService::class)->assign($member, [
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'template_id' => $data['template_id'] ?? null,
        ], auth()->user());
    }

    #[\Override]
    protected function getRedirectUrl(): string
    {
        return MemberWorkoutPlanResource::getUrl('index');
    }
}
