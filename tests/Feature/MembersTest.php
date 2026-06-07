<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

// ─── Web controller ──────────────────────────────────────────────────────────

it('index lists members', function (): void {
    Member::factory()->count(3)->create(['name' => 'Test Member']);

    $this->get(route('web.members.index'))
        ->assertOk()
        ->assertViewIs('members.index');
});

it('index filters members by search query', function (): void {
    Member::factory()->create(['name' => 'Andrei Popescu']);
    Member::factory()->create(['name' => 'Maria Ionescu']);

    $response = $this->get(route('web.members.index', ['search' => 'Andrei']));

    $response->assertOk();
    $members = $response->viewData('members');
    expect($members->where('name', 'Andrei Popescu')->count())->toBe(1)
        ->and($members->where('name', 'Maria Ionescu')->count())->toBe(0);
});

it('index filters members by status', function (): void {
    Member::factory()->create(['name' => 'Active Member', 'status' => 'active']);
    Member::factory()->create(['name' => 'Inactive Member', 'status' => 'inactive']);

    $response = $this->get(route('web.members.index', ['status' => 'active']));
    $members = $response->viewData('members');

    expect($members->where('name', 'Active Member')->count())->toBe(1)
        ->and($members->where('name', 'Inactive Member')->count())->toBe(0);
});

it('store creates a new member', function (): void {
    $this->post(route('web.members.store'), [
        'name' => 'Ion Test',
        'email' => 'ion.test@example.ro',
        'status' => 'active',
    ])->assertRedirect();

    expect(Member::where('email', 'ion.test@example.ro')->exists())->toBeTrue();
});

it('store validates required fields', function (): void {
    $this->post(route('web.members.store'), [])
        ->assertSessionHasErrors(['name', 'status']);
});

it('store rejects duplicate email', function (): void {
    Member::factory()->create(['email' => 'duplicate@example.ro']);

    $this->post(route('web.members.store'), [
        'name' => 'Another',
        'email' => 'duplicate@example.ro',
        'status' => 'active',
    ])->assertSessionHasErrors(['email']);
});

it('store uploads photo to public disk', function (): void {
    Storage::fake('public');

    $this->post(route('web.members.store'), [
        'name' => 'Photo Member',
        'status' => 'active',
        'photo' => UploadedFile::fake()->image('photo.jpg', 400, 400),
    ]);

    $member = Member::where('name', 'Photo Member')->first();
    expect($member)->not->toBeNull()
        ->and($member->photo)->not->toBeNull();

    Storage::disk('public')->assertExists($member->photo);
});

it('update changes member data', function (): void {
    $member = Member::factory()->create(['name' => 'Original Name']);

    $this->put(route('web.members.update', $member), [
        'name' => 'Updated Name',
        'status' => 'active',
    ])->assertRedirect();

    expect($member->fresh()->name)->toBe('Updated Name');
});

it('update replaces photo and deletes old one', function (): void {
    Storage::fake('public');

    $old = UploadedFile::fake()->image('old.jpg')->store('members', 'public');
    $member = Member::factory()->create(['photo' => $old]);

    $this->put(route('web.members.update', $member), [
        'name' => $member->name,
        'status' => 'active',
        'photo' => UploadedFile::fake()->image('new.jpg', 400, 400),
    ]);

    Storage::disk('public')->assertMissing($old);
    expect($member->fresh()->photo)->not->toBe($old);
});

it('destroy soft-deletes member', function (): void {
    $member = Member::factory()->create();

    $this->delete(route('web.members.destroy', $member))
        ->assertRedirect(route('web.members.index'));

    expect(Member::find($member->id))->toBeNull()
        ->and(Member::withTrashed()->find($member->id))->not->toBeNull();
});

it('destroy deletes photo from storage', function (): void {
    Storage::fake('public');
    $photo = UploadedFile::fake()->image('del.jpg')->store('members', 'public');
    $member = Member::factory()->create(['photo' => $photo]);

    $this->delete(route('web.members.destroy', $member));

    Storage::disk('public')->assertMissing($photo);
});

// ─── QR code ─────────────────────────────────────────────────────────────────

it('qr page renders svg for authenticated user', function (): void {
    $member = Member::factory()->create();

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
