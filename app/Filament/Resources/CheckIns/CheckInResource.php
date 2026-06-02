<?php

namespace App\Filament\Resources\CheckIns;

use App\Filament\Resources\CheckIns\Pages\ListCheckIns;
use App\Models\CheckIn;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CheckInResource extends Resource
{
    protected static ?string $model = CheckIn::class;

    public static function getModelLabel(): string
    {
        return __('app.checkins.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.checkins.plural');
    }

    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    /**
     * No write operations — check-ins are recorded via QR scan only.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('app.fields.member'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('checked_in_at')
                    ->label(__('app.checkins.checked_in'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('checked_out_at')
                    ->label(__('app.checkins.checked_out'))
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('duration')
                    ->label(__('app.checkins.duration'))
                    ->state(fn (CheckIn $record): string => $record->durationMinutes() !== null
                        ? __('app.checkins.minutes', ['min' => $record->durationMinutes()])
                        : '—')
                    ->sortable(false),
                TextColumn::make('subscription.plan.name')
                    ->label(__('app.fields.plan'))
                    ->placeholder('—'),
                TextColumn::make('method')
                    ->label(__('app.fields.method'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'qr' ? 'info' : 'gray'),
            ])
            ->defaultSort('checked_in_at', 'desc')
            ->filters([
                SelectFilter::make('period')
                    ->label(__('app.checkins.today'))
                    ->options([
                        'today' => __('app.checkins.today'),
                        'this_week' => __('app.checkins.this_week'),
                        'this_month' => __('app.checkins.this_month'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'today' => $query->whereDate('checked_in_at', today()),
                            'this_week' => $query->whereBetween('checked_in_at', [now()->startOfWeek(), now()->endOfWeek()]),
                            'this_month' => $query->whereBetween('checked_in_at', [now()->startOfMonth(), now()->endOfMonth()]),
                            default => $query,
                        };
                    }),
                SelectFilter::make('method')
                    ->label(__('app.fields.method'))
                    ->options(['qr' => 'QR', 'manual' => 'Manual']),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCheckIns::route('/'),
        ];
    }
}
