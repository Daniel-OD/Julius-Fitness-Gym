<?php

namespace App\Filament\Resources\CheckIns\Pages;

use App\Filament\Resources\CheckIns\CheckInResource;
use App\Models\CheckIn;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCheckIns extends ListRecords
{
    protected static string $resource = CheckInResource::class;

    public ?string $activeTab = 'today';

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [];
    }

    #[\Override]
    public function getTabs(): array
    {
        return [
            'today' => Tab::make(__('app.checkins.today'))
                ->badge(fn (): int => CheckIn::query()
                    ->whereDate('checked_in_at', today())
                    ->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereDate('checked_in_at', today())),
            'this_week' => Tab::make(__('app.checkins.this_week'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereBetween('checked_in_at', [now()->startOfWeek(), now()->endOfWeek()])),
            'this_month' => Tab::make(__('app.checkins.this_month'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereBetween('checked_in_at', [now()->startOfMonth(), now()->endOfMonth()])),
            'all' => Tab::make(__('app.common.all')),
        ];
    }
}
