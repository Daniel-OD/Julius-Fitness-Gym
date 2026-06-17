<?php

use App\Filament\Resources\Members\Pages\EditMember;
use App\Jobs\SendPasswordResetEmail;
use App\Models\Member;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

it('admin can reset a member password from edit page', function (): void {
    Queue::fake();

    $admin = adminPanelUser();
    $member = Member::factory()->create([
        'password' => 'OldPassword1!',
    ]);

    $this->actingAs($admin);

    Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
        ->callAction('reset_password')
        ->assertNotified();

    Queue::assertPushed(SendPasswordResetEmail::class, function (SendPasswordResetEmail $job) use ($member): bool {
        return $job->recipientType === 'member'
            && $job->recipientId === $member->id
            && $job->plainPassword !== '';
    });
});

it('hides member password reset when email is missing', function (): void {
    $admin = adminPanelUser();
    $member = Member::factory()->create([
        'email' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
        ->assertActionHidden('reset_password');
});
