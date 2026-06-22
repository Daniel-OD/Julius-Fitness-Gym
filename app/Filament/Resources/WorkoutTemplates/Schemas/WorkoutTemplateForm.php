<?php

namespace App\Filament\Resources\WorkoutTemplates\Schemas;

use App\Enums\WorkoutDifficulty;
use App\Models\Exercise;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkoutTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make('name')->label(__('app.fields.name'))->required(),
            Textarea::make('description')->label(__('app.fields.description')),
            Select::make('difficulty')
                ->label(__('app.fitness.difficulty'))
                ->options(collect(WorkoutDifficulty::cases())->mapWithKeys(
                    fn (WorkoutDifficulty $d): array => [$d->value => $d->getLabel()],
                )->all())
                ->required()
                ->native(false),
            TextInput::make('duration_minutes')->label(__('app.fitness.duration_minutes'))->numeric(),
            Toggle::make('is_public')->label(__('app.fitness.is_public'))->default(false),
            Repeater::make('exercises')
                ->relationship()
                ->label(__('app.fitness.template_exercises'))
                ->schema([
                    Select::make('exercise_id')
                        ->label(__('app.resources.exercises.singular'))
                        ->options(fn (): array => Exercise::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->required(),
                    TextInput::make('sets')->label(__('app.fitness.sets'))->numeric(),
                    TextInput::make('reps')->label(__('app.fitness.reps'))->numeric(),
                    TextInput::make('duration_seconds')->label(__('app.fitness.duration_seconds'))->numeric(),
                    TextInput::make('rest_seconds')->label(__('app.fitness.rest_seconds'))->numeric(),
                    TextInput::make('order')->label(__('app.fields.sort_order'))->numeric()->default(0),
                    Textarea::make('notes')->label(__('app.fields.note'))->rows(2),
                ])
                ->reorderable()
                ->orderColumn('order')
                ->columns(2)
                ->addActionLabel(__('app.fitness.add_exercise')),
        ]);
    }
}
