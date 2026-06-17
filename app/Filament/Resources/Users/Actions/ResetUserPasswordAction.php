<?php

namespace App\Filament\Resources\Users\Actions;

use App\Models\User;
use App\Services\Auth\PasswordResetService;
use App\Services\Email\PasswordResetEmailService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ResetUserPasswordAction
{
    public static function make(): Action
    {
        return Action::make('reset_password')
            ->label(__('app.actions.reset_password'))
            ->icon('heroicon-o-key')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading(__('app.actions.reset_password'))
            ->modalDescription(__('app.auth.reset_password_confirm'))
            ->visible(fn (User $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->action(function (User $record): void {
                $plainPassword = app(PasswordResetService::class)->resetUserPassword($record);

                app(PasswordResetEmailService::class)->queueUserPasswordReset(
                    $record->id,
                    $plainPassword,
                    auth()->id(),
                );

                Notification::make()
                    ->title(__('app.notifications.password_reset_queued'))
                    ->success()
                    ->body(__('app.notifications.password_reset_queued_body', ['email' => $record->email]))
                    ->send();
            });
    }
}
