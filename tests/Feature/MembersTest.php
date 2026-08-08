<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = adminPanelUser();
    actingAs($this->user);
});

// ─── Member model ────────────────────────────────────────────────────────────

it('auto-generates code and checkin_token on create', function (): void {
    $member = Member::factory()->create(['code' => null, 'checkin_token' => null]);

    expect($member->code)->not->toBeNull()
        ->and($member->checkin_token)->not->toBeNull()
        ->and(strlen((string) $member->checkin_token))->toBe(32);
});

it('checkin_token is unique across members', function (): void {
    $tokens = Member::factory()->count(10)->create()
        ->pluck('checkin_token')
        ->unique();

    expect($tokens->count())->toBe(10);
});

it('rejects a duplicate member code at the database level', function (): void {
    $existing = Member::factory()->create();

    expect(fn () => Member::factory()->create(['code' => $existing->code]))
        ->toThrow(QueryException::class);
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

    get(route('web.members.qr', $member))
        ->assertOk()
        ->assertViewIs('members.qr')
        ->assertSee($member->name, false)
        ->assertSee('<svg', false);
});

it('qr page generates checkin_token for legacy members', function (): void {
    $member = Member::factory()->create();
    $member->forceFill(['checkin_token' => null])->saveQuietly();

    get(route('web.members.qr', $member))->assertOk();

    expect($member->fresh()->checkin_token)->not->toBeNull();
});

it('qr download returns svg attachment', function (): void {
    $member = Member::factory()->create();

    get(route('web.members.qr.download', $member))
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml')
        ->assertSee('<svg', false);
});

it('qr routes require authentication', function (): void {
    auth()->logout();
    $member = Member::factory()->create();

    get(route('web.members.qr', $member))->assertRedirect();
    get(route('web.members.qr.download', $member))->assertRedirect();
});

it('qr routes deny users without View:Member permission', function (): void {
    actingAs(User::factory()->create());
    $member = Member::factory()->create();

    get(route('web.members.qr', $member))->assertForbidden();
    get(route('web.members.qr.download', $member))->assertForbidden();
});
