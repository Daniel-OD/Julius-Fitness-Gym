<?php

namespace App\Filament\Resources\WorkoutTemplates;

use App\Filament\Resources\WorkoutTemplates\Pages\CreateWorkoutTemplate;
use App\Filament\Resources\WorkoutTemplates\Pages\EditWorkoutTemplate;
use App\Filament\Resources\WorkoutTemplates\Pages\ListWorkoutTemplates;
use App\Filament\Resources\WorkoutTemplates\Schemas\WorkoutTemplateForm;
use App\Models\WorkoutTemplate;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkoutTemplateResource extends Resource
{
    protected static ?string $model = WorkoutTemplate::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.workout_templates.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.workout_templates.plural');
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return WorkoutTemplateForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('difficulty')->badge(),
            TextColumn::make('duration_minutes')->label(__('app.fitness.duration_minutes')),
            TextColumn::make('exercises_count')->counts('exercises')->label(__('app.fitness.exercises')),
            IconColumn::make('is_public')->boolean()->label(__('app.fitness.is_public')),
        ]);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListWorkoutTemplates::route('/'),
            'create' => CreateWorkoutTemplate::route('/create'),
            'edit' => EditWorkoutTemplate::route('/{record}/edit'),
        ];
    }
}
