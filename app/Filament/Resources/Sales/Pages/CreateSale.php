<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Enums\SaleStatus;
use App\Filament\Resources\Sales\SaleResource;
use App\Services\Shop\SaleService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.billing'),
            SaleResource::getNavigationLabel(),
            __('app.shop.new_sale'),
        ];
    }

    #[\Override]
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(SaleService::class)->create([
                'member_id' => $data['member_id'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'note' => $data['note'] ?? null,
                'status' => SaleStatus::Completed->value,
                'items' => collect($data['items'] ?? [])
                    ->map(fn (array $item): array => [
                        'product_id' => (int) $item['product_id'],
                        'quantity' => (int) $item['quantity'],
                    ])
                    ->all(),
            ], auth()->user());
        } catch (\InvalidArgumentException $exception) {
            Notification::make()
                ->title(__('app.notifications.failed'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            throw new Halt;
        }
    }

    #[\Override]
    protected function getRedirectUrl(): string
    {
        return SaleResource::getUrl('index');
    }
}
