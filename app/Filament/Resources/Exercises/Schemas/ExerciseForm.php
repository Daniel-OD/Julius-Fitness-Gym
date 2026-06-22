<?php

namespace App\Filament\Resources\Exercises\Schemas;

use App\Enums\ExerciseCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExerciseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->label(__('app.fields.name'))->required()->columnSpanFull(),
            Select::make('category')
                ->label(__('app.fields.category'))
                ->options(collect(ExerciseCategory::cases())->mapWithKeys(
                    fn (ExerciseCategory $c): array => [$c->value => $c->getLabel()],
                )->all())
                ->required()
                ->native(false),
            TextInput::make('equipment')->label(__('app.fitness.equipment')),
            TagsInput::make('muscle_groups')->label(__('app.fitness.muscle_groups'))->columnSpanFull(),
            Textarea::make('instructions')->label(__('app.fitness.instructions'))->columnSpanFull(),
            TextInput::make('video_url')->label(__('app.fitness.video_url'))->url()->columnSpanFull(),
            FileUpload::make('image')->label(__('app.fields.image'))->image()->disk('public')->directory('exercises'),
            Toggle::make('is_active')->label(__('app.fields.is_active'))->default(true),
        ]);
    }
}
