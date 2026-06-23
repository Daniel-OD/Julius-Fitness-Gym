<?php

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('renders change password page for authenticated member', function (): void {
    $member = Member::factory()->create(['password' => 'OldPassword1!', 'email_verified_at' => now()]);

    actingAs($member, 'member')
        ->get(route('member.password.edit'))
        ->assertSuccessful()
        ->assertSee(__('app.member_portal.change_password_button'));
});

it('redirects guests away from change password page', function (): void {
    get(route('member.password.edit'))
        ->assertRedirect(route('member.login'));
});

it('updates password with correct current password', function (): void {
    $member = Member::factory()->create(['password' => 'OldPassword1!', 'email_verified_at' => now()]);

    actingAs($member, 'member')
        ->put(route('member.password.update'), [
            'current_password' => 'OldPassword1!',
            'password' => 'NewPassword2@',
            'password_confirmation' => 'NewPassword2@',
        ])
        ->assertRedirect(route('member.dashboard'))
        ->assertSessionHas('success', __('app.member_portal.password_changed'));

    expect(Hash::check('NewPassword2@', (string) $member->fresh()->password))->toBeTrue();
});

it('rejects wrong current password', function (): void {
    $member = Member::factory()->create(['password' => 'OldPassword1!', 'email_verified_at' => now()]);

    actingAs($member, 'member')
        ->put(route('member.password.update'), [
            'current_password' => 'WrongPassword!',
            'password' => 'NewPassword2@',
            'password_confirmation' => 'NewPassword2@',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('OldPassword1!', (string) $member->fresh()->password))->toBeTrue();
});

it('rejects mismatched password confirmation', function (): void {
    $member = Member::factory()->create(['password' => 'OldPassword1!', 'email_verified_at' => now()]);

    actingAs($member, 'member')
        ->put(route('member.password.update'), [
            'current_password' => 'OldPassword1!',
            'password' => 'NewPassword2@',
            'password_confirmation' => 'DifferentPassword3#',
        ])
        ->assertSessionHasErrors('password');
});

it('rejects password shorter than 8 characters', function (): void {
    $member = Member::factory()->create(['password' => 'OldPassword1!', 'email_verified_at' => now()]);

    actingAs($member, 'member')
        ->put(route('member.password.update'), [
            'current_password' => 'OldPassword1!',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertSessionHasErrors('password');
});
