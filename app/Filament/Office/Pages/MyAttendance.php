<?php

namespace App\Filament\Office\Pages;

use App\Models\Attendance;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyAttendance extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $panel = 'office';

    protected static ?string $slug = 'my-attendance';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.office.pages.my-attendance';

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return __('app.hr.office.my_attendance');
    }

    #[\Override]
    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasStaffProfile() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Attendance::query()->where('user_id', auth()->id()))
            ->columns([
                TextColumn::make('date')
                    ->label(__('app.fields.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('check_in')
                    ->label(__('app.hr.fields.check_in'))
                    ->dateTime('H:i')
                    ->placeholder(__('app.placeholders.dash')),
                TextColumn::make('check_out')
                    ->label(__('app.hr.fields.check_out'))
                    ->dateTime('H:i')
                    ->placeholder(__('app.placeholders.dash')),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge(),
                TextColumn::make('method')
                    ->label(__('app.fields.method'))
                    ->badge(),
            ])
            ->defaultSort('date', 'desc')
            ->paginated([15, 30, 50]);
    }
}
