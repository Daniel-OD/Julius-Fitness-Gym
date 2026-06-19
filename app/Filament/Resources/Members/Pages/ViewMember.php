<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\Actions\ResetMemberPasswordAction;
use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use App\Services\Members\CreateMemberPortalAccountService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use RuntimeException;

/**
 * @property-read Member $record
 */
class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('qr')
                ->label(__('app.members.qr.title'))
                ->icon('heroicon-o-qr-code')
                ->url(fn (Member $record): string => route('web.members.qr', $record))
                ->openUrlInNewTab(),
            Action::make('createPortalAccount')
                ->label(__('app.client_portal.create_portal_account'))
                ->icon('heroicon-o-user-plus')
                ->visible(fn (Member $record): bool => $record->user_id === null && filled($record->email))
                ->requiresConfirmation()
                ->action(function (Member $record): void {
                    try {
                        app(CreateMemberPortalAccountService::class)->create($record);

                        Notification::make()
                            ->title(__('app.client_portal.portal_account_created', ['name' => $record->name]))
                            ->success()
                            ->send();
                    } catch (RuntimeException $exception) {
                        Notification::make()
                            ->title($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            EditAction::make(),
            ResetMemberPasswordAction::make(),
            DeleteAction::make(),
        ];
    }

    #[\Override]
    public function getTitle(): string
    {
        return __('app.titles.record', [
            'resource' => MemberResource::getModelLabel(),
            'name' => $this->record->name,
        ]);
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.memberships'),
            MemberResource::getUrl('index') => MemberResource::getNavigationLabel(),
            $this->record->name,
        ];
    }
}
