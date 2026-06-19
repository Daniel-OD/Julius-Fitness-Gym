<?php

namespace App\Notifications\Member;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class MemberVerifyEmailNotification extends VerifyEmail
{
    #[\Override]
    protected function verificationUrl(mixed $notifiable): string
    {
        return URL::temporarySignedRoute(
            'member.verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => password_hash((string) $notifiable->getEmailForVerification(), PASSWORD_BCRYPT),
            ]
        );
    }
}
