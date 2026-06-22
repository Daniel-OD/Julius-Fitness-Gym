<?php

namespace App\Filament\Resources\Shifts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class ShiftInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Fieldset::make(__('app.ui.details'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('app.fields.name')),
                        TextEntry::make('start_time')
                            ->label(__('app.hr.fields.start_time'))
                            ->formatStateUsing(fn (?string $state): string => $state ? substr($state, 0, 5) : '—'),
                        TextEntry::make('end_time')
                            ->label(__('app.hr.fields.end_time'))
                            ->formatStateUsing(fn (?string $state): string => $state ? substr($state, 0, 5) : '—'),
                        TextEntry::make('days_of_week')
                            ->label(__('app.hr.fields.days_of_week'))
                            ->formatStateUsing(function (?array $state): string {
                                if (! is_array($state) || $state === []) {
                                    return '—';
                                }

                                return collect($state)
                                    ->map(fn ($day): string => ShiftForm::daysOfWeekOptions()[(string) $day] ?? (string) $day)
                                    ->implode(', ');
                            }),
                        IconEntry::make('is_active')
                            ->label(__('app.fields.is_active'))
                            ->boolean(),
                    ]),
            ]);
    }
}
