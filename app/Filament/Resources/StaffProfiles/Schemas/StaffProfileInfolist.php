<?php

namespace App\Filament\Resources\StaffProfiles\Schemas;

use App\Enums\SalaryType;
use App\Helpers\Helpers;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class StaffProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Fieldset::make(__('app.ui.details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label(__('app.hr.fields.user')),
                        TextEntry::make('employee_code')
                            ->label(__('app.hr.fields.employee_code')),
                        TextEntry::make('department')
                            ->label(__('app.hr.fields.department'))
                            ->placeholder(__('app.placeholders.na')),
                        TextEntry::make('position')
                            ->label(__('app.hr.fields.position'))
                            ->placeholder(__('app.placeholders.na')),
                        TextEntry::make('hire_date')
                            ->label(__('app.hr.fields.hire_date'))
                            ->date()
                            ->placeholder(__('app.placeholders.na')),
                        TextEntry::make('base_salary')
                            ->label(__('app.hr.fields.base_salary'))
                            ->formatStateUsing(fn (?string $state): string => Helpers::formatCurrency((float) $state)),
                        TextEntry::make('salary_type')
                            ->label(__('app.hr.fields.salary_type'))
                            ->formatStateUsing(fn (SalaryType $state): string => $state->getLabel()),
                        TextEntry::make('emergency_contact')
                            ->label(__('app.hr.fields.emergency_contact'))
                            ->placeholder(__('app.placeholders.na')),
                        TextEntry::make('notes')
                            ->label(__('app.fields.note'))
                            ->placeholder(__('app.placeholders.na'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
