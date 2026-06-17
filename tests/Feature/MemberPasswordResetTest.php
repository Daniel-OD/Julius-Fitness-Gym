<?php

use App\Contracts\SettingsRepository;
use App\Jobs\SendPasswordResetEmail;
use App\Mail\PasswordResetMail;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

it('renders member forgot password page', function (): void {
    $this->get(route('member.password.request'))
        ->assertSuccessful()
        ->assertSee(__('app.member_portal.forgot_password_title'));
});

it('queues password reset email for existing member', function (): void {
    Queue::fake();

    $member = Member::factory()->create([
        'password' => 'OldPassword1!',
    ]);

    $this->post(route('member.password.email'), ['email' => $member->email])
        ->assertRedirect()
        ->assertSessionHas('status', __('app.member_portal.password_reset_sent'));

    $member->refresh();

    expect(Hash::check('OldPassword1!', (string) $member->password))->toBeFalse();

    Queue::assertPushed(SendPasswordResetEmail::class, function (SendPasswordResetEmail $job) use ($member): bool {
        return $job->recipientType === 'member'
            && $job->recipientId === $member->id
            && $job->plainPassword !== '';
    });
});

it('returns generic success message for unknown member email', function (): void {
    Queue::fake();

    $this->post(route('member.password.email'), ['email' => 'missing@example.com'])
        ->assertRedirect()
        ->assertSessionHas('status', __('app.member_portal.password_reset_sent'));

    Queue::assertNothingPushed();
});

it('sends member password reset email with new password', function (): void {
    Mail::fake();

    $member = Member::factory()->create();

    (new SendPasswordResetEmail(
        recipientType: 'member',
        recipientId: $member->id,
        plainPassword: 'GeneratedPass1',
    ))->handle(app(SettingsRepository::class));

    Mail::assertSent(PasswordResetMail::class, function (PasswordResetMail $mail) use ($member): bool {
        return $mail->hasTo($member->email)
            && $mail->plainPassword === 'GeneratedPass1'
            && $mail->loginUrl === route('member.login');
    });
});
