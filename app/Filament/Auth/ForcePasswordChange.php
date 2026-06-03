<?php

namespace App\Filament\Auth;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Forces a password change on first login (when must_change_password is true).
 *
 * Registered as a Filament page so it has a stable route in every panel.
 * RequirePasswordChange middleware redirects here before any other panel page.
 *
 * @property-read Schema $form
 */
class ForcePasswordChange extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.auth.force-password-change';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->must_change_password) {
            redirect(Filament::getUrl())->send();

            return;
        }

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('password')
                    ->label(__('app.fields.new_password'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->rule(Password::min(8)->mixedCase()->numbers())
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label(__('app.fields.confirm_password'))
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();

        if (! $user) {
            return;
        }

        $user->password = Hash::make($data['password']);
        $user->must_change_password = false;
        $user->save();

        Notification::make()
            ->title(__('app.auth.password_changed'))
            ->success()
            ->send();

        $this->redirect(Filament::getUrl());
    }

    /** @return array<int, Action> */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('app.actions.save'))
                ->submit('save'),
        ];
    }

    public function getTitle(): string
    {
        return __('app.auth.change_password');
    }
}
