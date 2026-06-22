<?php

namespace App\Support\Fitness;

use App\Models\Member;
use App\Models\User;

final class InstructorAccess
{
    public static function canAccessMember(User $user, Member $member): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if (! $user->isInstructor()) {
            return false;
        }

        return $user->assignedMembers()->whereKey($member->id)->exists();
    }

    public static function assignedMemberIds(User $user): ?array
    {
        if ($user->isAdministrator()) {
            return null;
        }

        if (! $user->isInstructor()) {
            return [];
        }

        return $user->assignedMembers()->pluck('members.id')->all();
    }
}
