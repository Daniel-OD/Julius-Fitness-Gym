<?php

namespace App\Services\Auth;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Generates a new random password and persists it for members or staff users.
 */
class PasswordResetService
{
    public function resetMemberPassword(Member $member): string
    {
        $plainPassword = Str::password(12);

        $member->forceFill(['password' => $plainPassword])->save();

        return $plainPassword;
    }

    public function resetUserPassword(User $user): string
    {
        $plainPassword = Str::password(16);

        $user->forceFill([
            'password' => $plainPassword,
            'must_change_password' => true,
            'remember_token' => Str::random(60),
        ])->save();

        return $plainPassword;
    }
}
