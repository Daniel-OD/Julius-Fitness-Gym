<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Support\AppConfig;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Select::make('user_id')
                    ->label(__('app.hr.fields.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date')
                    ->label(__('app.fields.date'))
                    ->default(fn (): string => now()->timezone(AppConfig::timezone())->toDateString())
                    ->required(),
                DateTimePicker::make('check_in')
                    ->label(__('app.hr.fields.check_in'))
                    ->seconds(false)
                    ->timezone(AppConfig::timezone()),
                DateTimePicker::make('check_out')
                    ->label(__('app.hr.fields.check_out'))
                    ->seconds(false)
                    ->timezone(AppConfig::timezone())
                    ->after('check_in'),
                Select::make('method')
                    ->label(__('app.fields.method'))
                    ->options(AttendanceMethod::class)
                    ->default(AttendanceMethod::Manual->value)
                    ->required(),
                Select::make('status')
                    ->label(__('app.fields.status'))
                    ->options(AttendanceStatus::class)
                    ->default(AttendanceStatus::Present->value)
                    ->required(),
                Textarea::make('note')
                    ->label(__('app.fields.note'))
                    ->rows(2),
            ]);
    }
}
