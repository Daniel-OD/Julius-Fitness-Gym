<?php

use App\Contracts\SettingsRepository;
use App\Support\MailConfigurator;
use Resend\Contracts\Client;

it('normalizes mail settings defaults', function (): void {
    app(SettingsRepository::class)->put([
        'general' => [],
        'invoice' => [],
        'member' => [],
        'charges' => [],
        'expenses' => [],
        'subscriptions' => [],
        'notifications' => ['email' => []],
        'backup' => [],
        'mail' => [],
    ]);

    $mail = app(SettingsRepository::class)->get()['mail'];

    expect($mail['driver'])->toBe('env')
        ->and($mail['smtp_port'])->toBe(587)
        ->and($mail['smtp_encryption'])->toBe('tls');
});

it('applies resend transport from settings', function (): void {
    config(['mail.default' => 'log']);

    MailConfigurator::apply([
        'mail' => [
            'driver' => 'resend',
            'resend_api_key' => 're_test_key',
            'from_address' => 'noreply@example.com',
            'from_name' => 'Test Gym',
        ],
    ]);

    expect(config('mail.default'))->toBe('resend')
        ->and(config('services.resend.key'))->toBe('re_test_key')
        ->and(config('mail.from.address'))->toBe('noreply@example.com')
        ->and(config('mail.from.name'))->toBe('Test Gym');
});

it('applies smtp transport from settings', function (): void {
    MailConfigurator::apply([
        'mail' => [
            'driver' => 'smtp',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 465,
            'smtp_username' => 'user',
            'smtp_password' => 'secret',
            'smtp_encryption' => 'ssl',
        ],
    ]);

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.example.com')
        ->and(config('mail.mailers.smtp.port'))->toBe(465)
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtps');
});

it('skips resend transport when package is unavailable', function (): void {
    config(['mail.default' => 'log']);

    MailConfigurator::apply([
        'mail' => [
            'driver' => 'resend',
            'resend_api_key' => 're_test_key',
        ],
    ]);

    expect(config('mail.default'))->toBe('log');
})->skip(interface_exists(Client::class), 'Resend package is installed in this environment.');

it('keeps env mailer when driver is env', function (): void {
    config(['mail.default' => 'array']);

    MailConfigurator::apply([
        'mail' => [
            'driver' => 'env',
            'from_address' => 'custom@example.com',
        ],
    ]);

    expect(config('mail.default'))->toBe('array')
        ->and(config('mail.from.address'))->toBe('custom@example.com');
});
