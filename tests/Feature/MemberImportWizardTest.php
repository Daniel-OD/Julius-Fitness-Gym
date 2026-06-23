<?php

use App\Enums\MemberImportField;
use App\Models\Member;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function wizardMapping(): array
{
    return [
        0 => MemberImportField::FirstName->value,
        1 => MemberImportField::LastName->value,
        2 => MemberImportField::Email->value,
        3 => MemberImportField::Contact->value,
        4 => MemberImportField::Dob->value,
        5 => MemberImportField::PlanName->value,
        6 => MemberImportField::PlanAmount->value,
        7 => MemberImportField::SubscriptionStart->value,
        8 => MemberImportField::SubscriptionEnd->value,
        9 => MemberImportField::Notes->value,
    ];
}

it('imports even when the uploaded file is deleted after the import starts', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $stored = 'member-imports/'.$user->id.'/'.Str::uuid().'.xlsx';
    Storage::disk('local')->put($stored, file_get_contents(public_path('templates/membri-template.xlsx')));

    $component = Livewire::test('filament.member-import-wizard')
        ->set('step', 3)
        ->set('storedExtension', 'xlsx')
        ->set('columnMapping', wizardMapping())
        ->set('storedFilePath', $stored);

    $component->call('startImport')->assertReturned(true);

    expect($component->get('importRunStarted'))->toBeTrue()
        ->and($component->get('importing'))->toBeTrue();

    // Simulate ephemeral storage: file vanishes between requests (the Railway scenario).
    Storage::disk('local')->delete($stored);

    $component->call('importChunk', 0);

    expect(Member::count())->toBe(3)
        ->and(Subscription::count())->toBe(3);
});

it('notifies the user instead of failing silently when the file is gone at start', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Livewire::test('filament.member-import-wizard')
        ->set('step', 3)
        ->set('storedExtension', 'xlsx')
        ->set('columnMapping', [0 => MemberImportField::Name->value, 1 => MemberImportField::Email->value])
        ->set('storedFilePath', 'member-imports/1/missing.xlsx');

    $component->call('startImport')->assertReturned(false);

    expect(Member::count())->toBe(0)
        ->and($component->get('importRunStarted'))->toBeFalse();

    $component->assertNotified(__('app.settings.import.errors.file_unavailable'));
});
