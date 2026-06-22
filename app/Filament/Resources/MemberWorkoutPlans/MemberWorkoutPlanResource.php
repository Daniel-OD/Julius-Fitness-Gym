<?php

namespace App\Filament\Resources\MemberWorkoutPlans;

use App\Filament\Resources\MemberWorkoutPlans\Pages\CreateMemberWorkoutPlan;
use App\Filament\Resources\MemberWorkoutPlans\Pages\ListMemberWorkoutPlans;
use App\Filament\Resources\MemberWorkoutPlans\Schemas\MemberWorkoutPlanForm;
use App\Models\MemberWorkoutPlan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MemberWorkoutPlanResource extends Resource
{
    protected static ?string $model = MemberWorkoutPlan::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.member_workout_plans.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.member_workout_plans.plural');
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return MemberWorkoutPlanForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('member.name')->label(__('app.fields.member'))->searchable(),
            TextColumn::make('name')->searchable(),
            TextColumn::make('start_date')->date(),
            TextColumn::make('end_date')->date(),
            TextColumn::make('status')->badge(),
        ]);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListMemberWorkoutPlans::route('/'),
            'create' => CreateMemberWorkoutPlan::route('/create'),
        ];
    }
}
