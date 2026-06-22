<?php

use App\Contracts\SettingsRepository;
use App\Models\Member;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(WhatsAppService::class);
});

function enableWhatsApp(string $provider = 'meta'): void
{
    app(SettingsRepository::class)->put([
        'notifications' => [
            'whatsapp' => [
                'enabled' => true,
                'provider' => $provider,
                'api_key' => 'test-api-key',
                'phone_number_id' => '123456789',
                'account_sid' => 'ACtest',
                'api_secret' => 'test-secret',
                'templates' => [
                    'subscription_expiry' => 'subscription_expiry_template',
                    'payment_confirmation' => 'payment_confirmation_template',
                    'welcome' => 'welcome_template',
                    'birthday' => 'birthday_template',
                ],
            ],
        ],
    ]);
}

it('returns false when whatsapp is disabled', function (): void {
    Http::fake();

    $result = $this->service->sendMessage('+40712345678', 'some_template', ['test']);

    expect($result)->toBeFalse();
    Http::assertNothingSent();
});

it('returns false when phone number is invalid', function (): void {
    enableWhatsApp();
    Http::fake(['*' => Http::response(['messages' => [['id' => 'test']]], 200)]);

    $result = $this->service->sendMessage('invalid', 'some_template', []);

    expect($result)->toBeFalse();
    Http::assertNothingSent();
});

it('sends via meta provider', function (): void {
    enableWhatsApp('meta');
    Http::fake(['https://graph.facebook.com/*' => Http::response(['messages' => [['id' => 'test']]], 200)]);

    $result = $this->service->sendMessage('+40712345678', 'welcome_template', ['John', 'Julius Gym']);

    expect($result)->toBeTrue();
    Http::assertSent(fn ($request): bool => str_contains($request->url(), 'graph.facebook.com'));
});

it('sends via twilio provider', function (): void {
    enableWhatsApp('twilio');
    Http::fake(['https://api.twilio.com/*' => Http::response(['sid' => 'SM123'], 201)]);

    $result = $this->service->sendMessage('+40712345678', 'welcome_template', ['John']);

    expect($result)->toBeTrue();
    Http::assertSent(fn ($request): bool => str_contains($request->url(), 'api.twilio.com'));
});

it('sends via vonage provider', function (): void {
    enableWhatsApp('vonage');
    Http::fake(['https://api.nexmo.com/*' => Http::response(['message_uuid' => 'abc'], 202)]);

    $result = $this->service->sendMessage('+40712345678', 'welcome_template', ['John']);

    expect($result)->toBeTrue();
    Http::assertSent(fn ($request): bool => str_contains($request->url(), 'api.nexmo.com'));
});

it('returns false when meta credentials are missing', function (): void {
    app(SettingsRepository::class)->put([
        'notifications' => [
            'whatsapp' => [
                'enabled' => true,
                'provider' => 'meta',
                'api_key' => '',
                'phone_number_id' => '',
            ],
        ],
    ]);

    Http::fake();

    $result = $this->service->sendMessage('+40712345678', 'template', []);

    expect($result)->toBeFalse();
    Http::assertNothingSent();
});

it('sendWelcome sends to member phone', function (): void {
    enableWhatsApp('meta');
    Http::fake(['https://graph.facebook.com/*' => Http::response(['messages' => [['id' => 'test']]], 200)]);

    $member = Member::factory()->create(['contact' => '+40712345678', 'name' => 'John Doe']);

    $result = $this->service->sendWelcome($member);

    expect($result)->toBeTrue();
});

it('sendBirthday sends to member phone', function (): void {
    enableWhatsApp('meta');
    Http::fake(['https://graph.facebook.com/*' => Http::response(['messages' => [['id' => 'test']]], 200)]);

    $member = Member::factory()->create(['contact' => '+40712345678', 'name' => 'John Doe']);

    $result = $this->service->sendBirthday($member);

    expect($result)->toBeTrue();
});

it('sendBirthday returns false when template not configured', function (): void {
    app(SettingsRepository::class)->put([
        'notifications' => [
            'whatsapp' => [
                'enabled' => true,
                'provider' => 'meta',
                'api_key' => 'test-key',
                'phone_number_id' => '123',
                'templates' => ['birthday' => ''],
            ],
        ],
    ]);

    Http::fake();

    $member = Member::factory()->create(['contact' => '+40712345678']);

    $result = $this->service->sendBirthday($member);

    expect($result)->toBeFalse();
    Http::assertNothingSent();
});

it('no http call is made when disabled even with valid phone', function (): void {
    Http::fake();

    $member = Member::factory()->create(['contact' => '+40712345678']);
    $result = $this->service->sendWelcome($member);

    expect($result)->toBeFalse();
    Http::assertNothingSent();
});
