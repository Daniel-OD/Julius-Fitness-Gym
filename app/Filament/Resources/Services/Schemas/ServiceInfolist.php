<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceInfolist
{
    /**
     * Configure the service "view" infolist schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('')
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('app.fields.name')),
                        TextEntry::make('description')
                            ->label(__('app.fields.description')),
                        TextEntry::make('icon')
                            ->label(__('app.fields.icon'))
                            ->badge(),
                        TextEntry::make('sort_order')
                            ->label(__('app.fields.sort_order')),
                        IconEntry::make('is_active')
                            ->label(__('app.fields.is_active'))
                            ->boolean(),
                        ImageEntry::make('images')
                            ->label(__('app.fields.gallery_images'))
                            ->disk('public')
                            ->stacked()
                            ->ring(2)
                            ->overlap(4),
                    ])->columns(1),
            ]);
    }
}
