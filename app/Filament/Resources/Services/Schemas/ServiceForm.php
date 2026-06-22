<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    /**
     * @return array<string, string>
     */
    public static function iconOptions(): array
    {
        return [
            'strength' => __('app.services.icons.strength'),
            'groups' => __('app.services.icons.groups'),
            'personal' => __('app.services.icons.personal'),
            'recovery' => __('app.services.icons.recovery'),
            'cardio' => __('app.services.icons.cardio'),
            'nutrition' => __('app.services.icons.nutrition'),
        ];
    }

    /**
     * Configure the service form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label(__('app.fields.name'))
                    ->placeholder(__('app.placeholders.service_name'))
                    ->required(),
                Textarea::make('description')
                    ->placeholder(__('app.placeholders.service_description'))
                    ->label(__('app.fields.description'))
                    ->required(),
                Select::make('icon')
                    ->label(__('app.fields.icon'))
                    ->options(self::iconOptions())
                    ->searchable(),
                TextInput::make('sort_order')
                    ->label(__('app.fields.sort_order'))
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label(__('app.fields.is_active'))
                    ->default(true),
                FileUpload::make('images')
                    ->label(__('app.fields.gallery_images'))
                    ->multiple()
                    ->image()
                    ->disk('public')
                    ->directory('services')
                    ->maxSize(10240)
                    ->reorderable()
                    ->appendFiles(),
            ]);
    }
}
