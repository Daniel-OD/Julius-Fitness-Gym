<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ─── Member model ────────────────────────────────────────────────────────────

it('auto-generates code and checkin_token on create', function (): void {
    $member = Member::factory()->create(['code' => null, 'checkin_token' => null]);

    expect($member->code)->not->toBeNull()
        ->and($member->checkin_token)->not->toBeNull()
        ->and(strlen($member->checkin_token))->toBe(32);
});

it('checkin_token is unique across members', function (): void {
    $tokens = Member::factory()->count(10)->create()
        ->pluck('checkin_token')
        ->unique();

    expect($tokens->count())->toBe(10);
});

it('member soft delete does not permanently remove record', function (): void {
    $member = Member::factory()->create();
    $id = $member->id;

    $member->delete();

    expect(Member::find($id))->toBeNull()
        ->and(Member::withTrashed()->find($id))->not->toBeNull();
});

// ─── QR code ─────────────────────────────────────────────────────────────────

it('qr page renders svg for authenticated user', function (): void {
    $member = Member::factory()->create(['name' => 'QR Test Member']);

    $this->get(route('web.members.qr', $member))
        ->assertOk()
        ->assertViewIs('members.qr')
        ->assertSee($member->name, false)
        ->assertSee('<svg', false);
});

it('qr page generates checkin_token for legacy members', function (): void {
    $member = Member::factory()->create();
    $member->forceFill(['checkin_token' => null])->saveQuietly();

    $this->get(route('web.members.qr', $member))->assertOk();

    expect($member->fresh()->checkin_token)->not->toBeNull();
});

it('qr download returns svg attachment', function (): void {
    $member = Member::factory()->create();

    $this->get(route('web.members.qr.download', $member))
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml')
        ->assertSee('<svg', false);
});

it('qr routes require authentication', function (): void {
    auth()->logout();
    $member = Member::factory()->create();

    $this->get(route('web.members.qr', $member))->assertRedirect();
    $this->get(route('web.members.qr.download', $member))->assertRedirect();
});
