<?php

use App\Filament\Resources\Members\Pages\ListMembers;
use App\Models\Member;
use Livewire\Livewire;

it('loads the admin members list page', function (): void {
    Member::factory()->count(2)->create();

    $this->actingAs(adminPanelUser())
        ->get(route('filament.admin.resources.members.index'))
        ->assertSuccessful();
});

it('renders the members table livewire component', function (): void {
    Member::factory()->count(2)->create();

    Livewire::actingAs(adminPanelUser())
        ->test(ListMembers::class)
        ->assertSuccessful();
});

it('loads the members list when a member has a null status', function (): void {
    $member = Member::factory()->create();
    $member->forceFill(['status' => null])->saveQuietly();

    $this->actingAs(adminPanelUser())
        ->get(route('filament.admin.resources.members.index'))
        ->assertSuccessful();
});
