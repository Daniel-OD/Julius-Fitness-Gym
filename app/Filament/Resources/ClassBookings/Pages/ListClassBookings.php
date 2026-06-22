<?php

namespace App\Filament\Resources\ClassBookings\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\ClassBookings\ClassBookingResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListClassBookings extends ListRecords
{
    protected static string $resource = ClassBookingResource::class;

    public function getTabs(): array
    {
        return [
            'upcoming' => ListRecords\Tab::make(__('app.classes.tabs.upcoming'))
                ->modifyQueryUsing(fn (Builder $q) => $q
                    ->whereDate('booked_date', '>=', today())
                    ->where('status', BookingStatus::Booked)),
            'today' => ListRecords\Tab::make(__('app.classes.tabs.today'))
                ->modifyQueryUsing(fn (Builder $q) => $q->whereDate('booked_date', today())),
            'past' => ListRecords\Tab::make(__('app.classes.tabs.past'))
                ->modifyQueryUsing(fn (Builder $q) => $q->whereDate('booked_date', '<', today())),
            'cancelled' => ListRecords\Tab::make(__('app.classes.tabs.cancelled'))
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', BookingStatus::Cancelled)),
            'all' => ListRecords\Tab::make(__('app.common.all')),
        ];
    }
}
