<?php

namespace App\Filament\Resources\StaffProfiles\Schemas;

use App\Enums\SalaryType;
use App\Helpers\Helpers;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class StaffProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('app.ui.details'))
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label(__('app.hr.fields.user'))
                            ->relationship(
                                'user',
                                'name',
                                modifyQueryUsing: fn ($query, string $operation) => $operation === 'create'
                                    ? $query->whereDoesntHave('staffProfile')
                                    : $query,
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('department')
                            ->label(__('app.hr.fields.department'))
                            ->maxLength(255),
                        TextInput::make('position')
                            ->label(__('app.hr.fields.position'))
                            ->maxLength(255),
                        DatePicker::make('hire_date')
                            ->label(__('app.hr.fields.hire_date')),
                        TextInput::make('base_salary')
                            ->label(__('app.hr.fields.base_salary'))
                            ->prefix(Helpers::getCurrencySymbol())
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters([','])
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Select::make('salary_type')
                            ->label(__('app.hr.fields.salary_type'))
                            ->options(SalaryType::class)
                            ->default(SalaryType::Monthly->value)
                            ->required(),
                    ]),
                Section::make(__('app.hr.fields.bank_details'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('bank_details.iban')
                            ->label(__('app.hr.fields.iban'))
                            ->maxLength(255),
                        TextInput::make('bank_details.bank_name')
                            ->label(__('app.hr.fields.bank_name'))
                            ->maxLength(255),
                    ]),
                Section::make()
                    ->schema([
                        TextInput::make('emergency_contact')
                            ->label(__('app.hr.fields.emergency_contact'))
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->label(__('app.fields.note'))
                            ->rows(3),
                    ]),
            ]);
    }
}
