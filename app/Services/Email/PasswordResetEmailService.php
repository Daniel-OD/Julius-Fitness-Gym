<?php

namespace App\Services\Email;

use App\Jobs\SendPasswordResetEmail;

/**
 * Queue password-reset emails that include the newly generated password.
 */
final class PasswordResetEmailService
{
    public function queueMemberPasswordReset(int $memberId, string $plainPassword): void
    {
        SendPasswordResetEmail::dispatch(
            recipientType: 'member',
            recipientId: $memberId,
            plainPassword: $plainPassword,
        )->afterCommit();
    }

    public function queueUserPasswordReset(int $userId, string $plainPassword, ?int $actorId = null): void
    {
        SendPasswordResetEmail::dispatch(
            recipientType: 'user',
            recipientId: $userId,
            plainPassword: $plainPassword,
            actorId: $actorId,
        )->afterCommit();
    }
}
