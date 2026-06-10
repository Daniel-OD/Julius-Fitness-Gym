<?php

use App\Models\Member;
use App\Support\PublicLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders homepage in romanian by default', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Antrenează.')
        ->assertSee('Servicii');
});

it('switches homepage language via locale route', function (): void {
    $this->from(route('home'))
        ->get(route('public.locale', ['locale' => 'en']))
        ->assertRedirect(route('home'))
        ->assertSessionHas(PublicLocale::SESSION_KEY, 'en');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Train.')
        ->assertSee('Services');
});

it('does not apply public locale on member portal routes', function (): void {
    $member = Member::factory()->create([
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $this->withSession([PublicLocale::SESSION_KEY => 'it'])
        ->actingAs($member, 'member')
        ->get(route('member.plans'))
        ->assertOk()
        ->assertSee(__('app.member.plans.title', [], 'ro'))
        ->assertDontSee(__('public.hero.title_train', [], 'it'));
});

it('rejects unsupported public locale', function (): void {
    $this->from(route('home'))
        ->get('/locale/fr')
        ->assertNotFound();
});
