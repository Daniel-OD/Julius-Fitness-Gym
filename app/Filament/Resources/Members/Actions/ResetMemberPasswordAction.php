<?php

namespace App\Filament\Resources\Members\Actions;

use App\Models\Member;
use App\Services\Auth\PasswordResetService;
use App\Services\Email\PasswordResetEmailService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ResetMemberPasswordAction
{
    public static function make(): Action
    {
        return Action::make('reset_password')
            ->label(__('app.actions.reset_password'))
            ->icon('heroicon-o-key')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading(__('app.actions.reset_password'))
            ->modalDescription(__('app.auth.reset_member_password_confirm'))
            ->visible(fn (Member $record): bool => filled($record->email) && (auth()->user()?->can('update', $record) ?? false))
            ->action(function (Member $record): void {
                $plainPassword = app(PasswordResetService::class)->resetMemberPassword($record);

                app(PasswordResetEmailService::class)->queueMemberPasswordReset(
                    $record->id,
                    $plainPassword,
                );

                Notification::make()
                    ->title(__('app.notifications.password_reset_queued'))
                    ->success()
                    ->body(__('app.notifications.password_reset_queued_body', ['email' => $record->email]))
                    ->send();
            });
    }
}
