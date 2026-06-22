<?php

namespace App\Filament\Resources\MemberWorkoutPlans\Schemas;

use App\Models\Member;
use App\Models\WorkoutTemplate;
use App\Support\Fitness\InstructorAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MemberWorkoutPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Select::make('member_id')
                ->label(__('app.fields.member'))
                ->options(function (): array {
                    $user = auth()->user();
                    $query = Member::query()->orderBy('name');
                    $ids = $user ? InstructorAccess::assignedMemberIds($user) : null;
                    if (is_array($ids)) {
                        $query->whereIn('id', $ids);
                    }

                    return $query->pluck('name', 'id')->all();
                })
                ->searchable()
                ->required(),
            TextInput::make('name')->label(__('app.fields.name'))->required(),
            Select::make('template_id')
                ->label(__('app.resources.workout_templates.singular'))
                ->options(fn (): array => WorkoutTemplate::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->dehydrated(false),
            DatePicker::make('start_date')->label(__('app.fields.start_date'))->required(),
            DatePicker::make('end_date')->label(__('app.fields.end_date')),
            Textarea::make('notes')->label(__('app.fields.note'))->rows(3),
        ]);
    }
}
