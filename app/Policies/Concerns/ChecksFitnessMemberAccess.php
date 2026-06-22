<?php

namespace App\Policies\Concerns;

use App\Models\Member;
use App\Support\Fitness\InstructorAccess;
use Illuminate\Foundation\Auth\User as AuthUser;

trait ChecksFitnessMemberAccess
{
    protected function canAccessMember(AuthUser $authUser, Member $member): bool
    {
        return InstructorAccess::canAccessMember($authUser, $member);
    }
}
