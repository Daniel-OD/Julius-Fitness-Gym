<?php

use App.Contracts\SettingsRepository;
use App.Filament\Resources\Users\Pages\EditUser;
use App.Jobs\SendPasswordResetEmail;
use App.Mail\PasswordResetMail;
use App.Models\User;
use App.Services\Auth\PasswordResetService;
use Illuminate.Support.Facades\Mail;
use Illuminate.Support.Facades\Queue;
use Livewire\Livewire;

it('admin can reset a user password from edit page', function (): void {
    Queue::fake();

    $admin = adminPanelUser();
    $target = User::factory()->create([
        'must_change_password' => false,
    ]);

    actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $target->getRouteKey()])
        ->callAction('reset_password')
        ->assertNotified();

    $target->refresh();

    expect($target->must_change_password)->toBeTrue();

    Queue::assertPushed(SendPasswordResetEmail::class, fn (SendPasswordResetEmail $job): bool => $job->recipientType === 'user'
        && $job->recipientId === $target->id
        && $job->plainPassword !== '');
});

it('password reset service sets must change password for staff users', function (): void {
    $user = User::factory()->create([
        'must_change_password' => false,
    ]);

    $plainPassword = app(PasswordResetService::class)->resetUserPassword($user);

    $user->refresh();

    expect($user->must_change_password)->toBeTrue()
        ->and($plainPassword)->not->toBeEmpty();
});

it('sends staff password reset email with new password', function (): void {
    Mail::fake();

    $user = User::factory()->create();

    new SendPasswordResetEmail(
        recipientType: 'user',
        recipientId: $user->id,
        plainPassword: 'StaffPass123!',
    )->handle(app(SettingsRepository::class));

    Mail::assertSent(PasswordResetMail::class, fn (PasswordResetMail $mail): bool => $mail->hasTo($user->email)
        && $mail->plainPassword === 'StaffPass123!'
        && $mail->loginUrl === route('filament.admin.auth.login'));
});
