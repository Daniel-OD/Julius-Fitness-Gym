<?php

namespace App\Filament\Widgets\Office;

use App\Models\CheckIn;
use App\Services\CheckIns\CheckInService;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only list of members currently in the gym (or who left within the grace window).
 */
class OfficePresentNowWidget extends TableWidget
{
    protected static ?int $sort = -45;

    protected ?string $pollingInterval = '30s';

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.office.present_now'))
            ->query(fn (): Builder => app(CheckInService::class)->presentNowQuery())
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('app.fields.member'))
                    ->description(fn (CheckIn $record): string => (string) ($record->member?->code ?? ''))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('checked_in_at')
                    ->label(__('app.office.entry_time'))
                    ->time('H:i')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('presence_status')
                    ->label(__('app.office.presence_status'))
                    ->state(function (CheckIn $record): string {
                        $service = app(CheckInService::class);

                        if ($record->checked_out_at === null) {
                            return __('app.office.present_status_in', [
                                'time' => $record->checked_in_at
                                    ->timezone(AppConfig::timezone())
                                    ->format('H:i'),
                            ]);
                        }

                        $minutes = (int) CarbonImmutable::parse($record->checked_out_at)
                            ->diffInMinutes(CarbonImmutable::now(AppConfig::timezone()));

                        return __('app.office.present_status_left', ['minutes' => max($minutes, 1)]);
                    })
                    ->color(fn (CheckIn $record): string => $record->checked_out_at === null ? 'success' : 'gray')
                    ->badge(),
            ])
            ->emptyStateHeading(__('app.office.no_present_now'))
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
    }
}
