<?php

namespace App\Filament\Resources\GymClasses\Schemas;

use App\Models\User;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;

class GymClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('app.ui.details'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('app.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('app.fields.description'))
                            ->rows(3),
                        Select::make('instructor_id')
                            ->label(__('app.classes.fields.instructor'))
                            ->placeholder(__('app.classes.placeholders.select_instructor'))
                            ->options(fn (): array => User::query()
                                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['instructor', 'super_admin', 'owner']))
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->nullable()
                            ->getOptionLabelFromRecordUsing(function (User $record): string {
                                $name = html_entity_decode($record->name, ENT_QUOTES, 'UTF-8');
                                $url = ! empty($record->photo) ? e($record->photo) : "https://ui-avatars.com/api/?background=000&color=fff&name={$name}";

                                return Blade::render(
                                    '<div class="flex items-center gap-2 h-9">
                                    <x-filament::avatar src="{{ $url }}" alt="{{ $name }}" size="sm" />
                                    <span class="ml-2">{{ $name }}</span>
                                 </div>',
                                    compact('url', 'name')
                                );
                            })
                            ->allowHtml(),
                        TextInput::make('capacity')
                            ->label(__('app.classes.fields.capacity'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(10),
                        TextInput::make('duration_minutes')
                            ->label(__('app.classes.fields.duration_minutes'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(60)
                            ->suffix(__('app.classes.units.minutes')),
                        ColorPicker::make('color')
                            ->label(__('app.classes.fields.color'))
                            ->default('#6366f1'),
                        Toggle::make('is_active')
                            ->label(__('app.fields.is_active'))
                            ->default(true),
                    ])->columns(3)->columnSpanFull(),
            ]);
    }
}
