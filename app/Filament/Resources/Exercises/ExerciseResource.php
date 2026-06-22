<?php

namespace App\Filament\Resources\Exercises;

use App\Filament\Resources\Exercises\Pages\ListExercises;
use App\Filament\Resources\Exercises\Schemas\ExerciseForm;
use App\Filament\Resources\Exercises\Tables\ExerciseTable;
use App\Models\Exercise;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ExerciseResource extends Resource
{
    protected static ?string $model = Exercise::class;

    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    public static function getModelLabel(): string
    {
        return __('app.resources.exercises.singular');
    }

    #[\Override]
    public static function getPluralModelLabel(): string
    {
        return __('app.resources.exercises.plural');
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return ExerciseForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ExerciseTable::configure($table);
    }

    #[\Override]
    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('category')->badge(),
            TextEntry::make('muscle_groups')->badge(),
            TextEntry::make('instructions')->columnSpanFull(),
            TextEntry::make('video_url')->url(fn (?string $state): ?string => $state),
            ImageEntry::make('image')->disk('public'),
        ]);
    }

    #[\Override]
    public static function getPages(): array
    {
        return ['index' => ListExercises::route('/')];
    }
}
