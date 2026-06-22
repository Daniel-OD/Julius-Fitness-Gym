<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Enums\SaleStatus;
use App\Helpers\Helpers;
use App\Models\Sale;
use App\Services\Shop\SaleService;
use App\Support\Billing\PaymentMethod;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SaleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('app.fields.date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('member.name')
                    ->label(__('app.fields.member'))
                    ->placeholder(__('app.shop.walk_in'))
                    ->searchable(),
                TextColumn::make('cashier.name')
                    ->label(__('app.shop.cashier'))
                    ->placeholder('—'),
                TextColumn::make('total')
                    ->label(__('app.fields.total'))
                    ->formatStateUsing(fn (?float $state): string => Helpers::formatCurrency($state)),
                TextColumn::make('payment_method')
                    ->label(__('app.fields.payment_method'))
                    ->formatStateUsing(fn (?string $state): string => PaymentMethod::channelLabel($state)),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label(__('app.shop.items')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(SaleStatus::cases())->mapWithKeys(
                        fn (SaleStatus $status): array => [$status->value => $status->getLabel()],
                    )->all()),
                SelectFilter::make('member_id')
                    ->label(__('app.fields.member'))
                    ->relationship('member', 'name')
                    ->searchable(),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from')->label(__('app.fields.date_from')),
                        DatePicker::make('to')->label(__('app.fields.date_to')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '>=', $date))
                            ->when($data['to'] ?? null, fn (Builder $q, string $date): Builder => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('2xl'),
                ActionGroup::make([
                    Action::make('cancel')
                        ->label(__('app.actions.cancel'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Sale $record): bool => $record->status === SaleStatus::Completed)
                        ->action(function (Sale $record): void {
                            app(SaleService::class)->cancel($record);

                            Notification::make()
                                ->title(__('app.shop.sale_cancelled'))
                                ->success()
                                ->send();
                        }),
                ])->dropdown(false),
            ])
            ->toolbarActions([
                BulkAction::make('cancel_selected')
                    ->label(__('app.shop.cancel_sales'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $service = app(SaleService::class);

                        foreach ($records as $record) {
                            if ($record instanceof Sale && $record->status === SaleStatus::Completed) {
                                $service->cancel($record);
                            }
                        }

                        Notification::make()
                            ->title(__('app.shop.sales_cancelled'))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
