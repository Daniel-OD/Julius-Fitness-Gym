<?php

namespace App\Services\Members;

use App\Enums\Status;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\ClientRoleSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

class CreateMemberPortalAccountService
{
    public function create(Member $member): User
    {
        if ($member->user_id !== null) {
            throw new RuntimeException(__('app.client_portal.portal_account_exists'));
        }

        $email = trim((string) $member->email);

        if ($email === '') {
            throw new RuntimeException(__('app.client_portal.portal_account_no_email'));
        }

        if (User::query()->where('email', $email)->exists()) {
            throw new RuntimeException(__('app.client_portal.portal_account_email_taken'));
        }

        (new ClientRoleSeeder)->run();

        $user = User::query()->create([
            'name' => (string) ($member->name ?? $email),
            'email' => $email,
            'password' => Hash::make(Str::password(16)),
            'contact' => $member->contact,
            'gender' => $member->gender ?? 'other',
            'status' => Status::Active,
            'must_change_password' => true,
        ]);

        $role = Role::findByName('client', 'web');
        $user->assignRole($role);

        $member->user()->associate($user);
        $member->save();

        return $user;
    }
}
