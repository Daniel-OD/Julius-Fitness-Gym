<?php

namespace App\Filament\Widgets\Office;

use App\Models\CheckIn;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only list of members currently in the gym: checked in today and not yet
 * checked out. Polls every 30 seconds so the front desk sees live occupancy.
 */
class OfficePresentNowWidget extends TableWidget
{
    protected static ?int $sort = -45;

    protected ?string $pollingInterval = '30s';

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    /**
     * @return Builder<CheckIn>
     */
    public function getPresentNowQuery(): Builder
    {
        $today = CarbonImmutable::today(AppConfig::timezone())->toDateString();

        return CheckIn::query()
            ->with('member')
            ->whereDate('checked_in_at', $today)
            ->whereNull('checked_out_at')
            ->latest('checked_in_at');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('app.office.present_now'))
            ->query(fn (): Builder => $this->getPresentNowQuery())
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
            ])
            ->emptyStateHeading(__('app.office.no_present_now'))
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50]);
    }
}
