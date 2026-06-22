<?php

namespace App\Filament\Resources\Shifts\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShiftForm
{
    /**
     * @return array<int, string>
     */
    public static function daysOfWeekOptions(): array
    {
        return collect(range(0, 6))
            ->mapWithKeys(fn (int $day): array => [
                (string) $day => __('app.hr.days_of_week.'.$day),
            ])
            ->all();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label(__('app.fields.name'))
                    ->required()
                    ->maxLength(255),
                TimePicker::make('start_time')
                    ->label(__('app.hr.fields.start_time'))
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label(__('app.hr.fields.end_time'))
                    ->seconds(false)
                    ->required(),
                CheckboxList::make('days_of_week')
                    ->label(__('app.hr.fields.days_of_week'))
                    ->options(static::daysOfWeekOptions())
                    ->columns(4)
                    ->required()
                    ->bulkToggleable(),
                ColorPicker::make('color')
                    ->label(__('app.hr.fields.color'))
                    ->default('#6366f1'),
                Toggle::make('is_active')
                    ->label(__('app.fields.is_active'))
                    ->default(true),
            ]);
    }
}
