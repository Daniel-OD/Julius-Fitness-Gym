<?php

namespace App\Services\Email;

use App\Jobs\SendMemberPortalInvitation;

/**
 * Member portal email service.
 */
final class MemberPortalEmailService
{
    /**
     * Queue a portal invitation email for a member.
     */
    public function queuePortalInvitation(int $memberId, ?int $actorId = null): void
    {
        SendMemberPortalInvitation::dispatch(
            memberId: $memberId,
            actorId: $actorId,
        )->afterCommit();
    }
}
