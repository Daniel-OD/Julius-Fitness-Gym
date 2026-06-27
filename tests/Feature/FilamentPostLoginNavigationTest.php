<?php

use App\Filament\Auth\Login;
use App\Filament\Resources\Members\MemberResource;
use App\Support\FilamentSession;
use Filament\Facades\Filament;
use Livewire\Livewire;

it('locks panel and allows admin sidebar navigation after staff login', function (): void {
    $user = adminPanelUser();

    $this->get('/staff/login');

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect(Filament::getPanel('admin')->getUrl());

    expect(FilamentSession::authenticatedPanelId())->toBe('admin');

    $this->get(MemberResource::getUrl('index'))
        ->assertSuccessful();
});

it('does not log out when panel lock is missing on first admin request', function (): void {
    $user = adminPanelUser();

    $this->actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertSuccessful();

    expect(FilamentSession::authenticatedPanelId())->toBe('admin');
});

it('redirects to staff login when admin is opened while locked to office', function (): void {
    $user = adminPanelUser();
    FilamentSession::lockToPanel('office');

    $this->actingAs($user)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('does not destroy session when authenticated user visits login without a panel lock', function (): void {
    $user = adminPanelUser();

    $this->actingAs($user)
        ->get('/staff/login')
        ->assertRedirect(Filament::getPanel('admin')->getUrl());

    expect(FilamentSession::authenticatedPanelId())->toBe('admin');
});

it('redirects unauthenticated admin requests to staff login', function (): void {
    $this->get(route('filament.admin.pages.dashboard'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('generates sidebar links on the request host when APP_URL differs', function (): void {
    config(['app.url' => 'http://wrong-host.test']);

    $user = adminPanelUser();

    $this->actingAs($user)
        ->get('https://julius-fitness-gym.test/admin/dashboard')
        ->assertSuccessful()
        ->assertSee('href="https://julius-fitness-gym.test/admin/members"', false);
});
