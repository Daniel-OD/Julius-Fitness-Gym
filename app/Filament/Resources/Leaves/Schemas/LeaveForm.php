<?php

namespace App\Filament\Resources\Leaves\Schemas;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class LeaveForm
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
                Select::make('type')
                    ->label(__('app.fields.type'))
                    ->options(LeaveType::class)
                    ->required(),
                DatePicker::make('start_date')
                    ->label(__('app.hr.fields.start_date'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => static::syncDays($get, $set)),
                DatePicker::make('end_date')
                    ->label(__('app.hr.fields.end_date'))
                    ->required()
                    ->afterOrEqual('start_date')
                    ->live()
                    ->afterStateUpdated(fn (Get $get, Set $set) => static::syncDays($get, $set)),
                TextInput::make('days')
                    ->label(__('app.hr.fields.days'))
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                Textarea::make('reason')
                    ->label(__('app.hr.fields.reason'))
                    ->rows(3),
                Select::make('status')
                    ->label(__('app.fields.status'))
                    ->options(LeaveStatus::class)
                    ->default(LeaveStatus::Pending->value)
                    ->visibleOn('edit')
                    ->required(),
            ]);
    }

    public static function syncDays(Get $get, Set $set): void
    {
        $start = $get('start_date');
        $end = $get('end_date');

        if (blank($start) || blank($end)) {
            return;
        }

        $days = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        $set('days', $days);
    }
}
