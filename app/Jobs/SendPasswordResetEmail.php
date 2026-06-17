<?php

namespace App\Jobs;

use App\Contracts\SettingsRepository;
use App\Mail\PasswordResetMail;
use App\Models\Member;
use App\Models\User;
use App\Support\AppConfig;
use App\Support\Data;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Send an email containing a newly generated password.
 */
class SendPasswordResetEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly string $recipientType,
        public readonly int $recipientId,
        public readonly string $plainPassword,
        public readonly ?int $actorId = null,
    ) {}

    public function handle(SettingsRepository $settingsRepository): void
    {
        $settings = $settingsRepository->get();
        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name')));
        $gymEmail = Data::string(data_get($settings, 'general.gym_email', ''));
        $gymContact = Data::string(data_get($settings, 'general.gym_contact', ''));
        $gymDisplayName = $gymName !== '' ? $gymName : 'Julius Fitness Gym';

        $recipient = $this->resolveRecipient($gymDisplayName);

        if ($recipient === null) {
            return;
        }

        [$recipientEmail, $recipientName, $loginUrl, $introLine, $loginButtonLabel] = $recipient;

        if (! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info('Skipping password reset email: recipient email missing.', [
                'recipient_type' => $this->recipientType,
                'recipient_id' => $this->recipientId,
            ]);

            return;
        }

        $mailable = new PasswordResetMail(
            subjectLine: Data::string(__('app.emails.password_reset_subject', ['gym' => $gymDisplayName])),
            gymName: $gymDisplayName,
            gymEmail: $gymEmail,
            gymContact: $gymContact,
            recipientName: $recipientName,
            plainPassword: $this->plainPassword,
            loginUrl: $loginUrl,
            introLine: $introLine,
            loginButtonLabel: $loginButtonLabel,
        );

        if (filled($gymEmail)) {
            $mailable->replyTo($gymEmail, $gymDisplayName);
        }

        Mail::to($recipientEmail)->send($mailable);
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string, 4: string}|null
     */
    private function resolveRecipient(string $gymName): ?array
    {
        if ($this->recipientType === 'member') {
            $member = Member::query()->find($this->recipientId);

            if (! $member instanceof Member) {
                return null;
            }

            return [
                Data::string($member->email),
                Data::string($member->name),
                route('member.login'),
                Data::string(__('app.emails.password_reset_member_line', ['gym' => $gymName])),
                Data::string(__('app.emails.password_reset_member_button')),
            ];
        }

        if ($this->recipientType === 'user') {
            $user = User::query()->find($this->recipientId);

            if (! $user instanceof User) {
                return null;
            }

            return [
                Data::string($user->email),
                Data::string($user->name),
                route('filament.admin.auth.login'),
                Data::string(__('app.emails.password_reset_user_line', ['gym' => $gymName])),
                Data::string(__('app.emails.password_reset_user_button')),
            ];
        }

        return null;
    }
}
