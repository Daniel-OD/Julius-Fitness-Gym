<?php

namespace App\Filament\Resources\PayrollPeriods\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollPeriodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('app.ui.details'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('label')
                            ->label(__('app.hr.fields.period'))
                            ->state(fn ($record): string => $record->label()),
                        TextEntry::make('status')
                            ->label(__('app.fields.status'))
                            ->badge(),
                        TextEntry::make('generated_at')
                            ->label(__('app.hr.fields.generated_at'))
                            ->dateTime()
                            ->placeholder(__('app.placeholders.dash')),
                        TextEntry::make('approver.name')
                            ->label(__('app.hr.fields.approved_by'))
                            ->placeholder(__('app.placeholders.dash')),
                    ]),
            ]);
    }
}
