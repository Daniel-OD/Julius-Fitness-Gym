<?php

use App\Contracts\SettingsRepository;
use App\Filament\Resources\Members\MemberResource;
use App\Http\Middleware\SetAppLocale;
use Filament\Facades\Filament;

it('includes locale middleware on the admin panel stack', function (): void {
    expect(Filament::getPanel('admin')->getMiddleware())->toContain(SetAppLocale::class);
});

it('renders the admin dashboard in romanian when settings locale is ro', function (): void {
    /** @var SettingsRepository $settings */
    $settings = app(SettingsRepository::class);
    $settings->put([
        ...$settings->get(),
        'general' => [
            ...($settings->get()['general'] ?? []),
            'locale' => 'ro',
        ],
    ]);

    $this->actingAs(adminPanelUser())
        ->get('/admin/dashboard')
        ->assertSuccessful()
        ->assertSee('Membri', false)
        ->assertSee('Tablou de bord', false);

    expect(app()->getLocale())->toBe('ro');
    expect(MemberResource::getNavigationLabel())->toBe('Membri');
});
