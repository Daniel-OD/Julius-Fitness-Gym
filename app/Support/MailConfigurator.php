<?php

namespace App\Support;

use App\Contracts\SettingsRepository;
use Resend\Contracts\Client;

/**
 * Applies outbound mail transport settings from application settings (settingsData.json).
 *
 * When driver is "env", Laravel uses .env / platform variables (Render, Railway, Docker).
 */
final class MailConfigurator
{
    public const DRIVER_ENV = 'env';

    public const DRIVER_LOG = 'log';

    public const DRIVER_RESEND = 'resend';

    public const DRIVER_SMTP = 'smtp';

    public const DRIVER_SENDMAIL = 'sendmail';

    /**
     * @return array<string, mixed>
     */
    public static function defaultMailSettings(): array
    {
        return [
            'driver' => self::DRIVER_ENV,
            'from_address' => '',
            'from_name' => '',
            'resend_api_key' => '',
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
        ];
    }

    public static function resendIsAvailable(): bool
    {
        return interface_exists(Client::class);
    }

    /**
     * @param  array<string, mixed>|null  $settings
     */
    public static function apply(?array $settings = null): void
    {
        if ($settings === null) {
            $settings = app(SettingsRepository::class)->get();
        }

        /** @var array<string, mixed> $mail */
        $mail = is_array($settings['mail'] ?? null) ? $settings['mail'] : [];

        $driver = (string) ($mail['driver'] ?? self::DRIVER_ENV);

        if ($driver === self::DRIVER_ENV) {
            self::applyFromAddress($mail);

            return;
        }

        if ($driver === self::DRIVER_LOG) {
            config(['mail.default' => 'log']);
            self::applyFromAddress($mail);

            return;
        }

        if ($driver === self::DRIVER_RESEND) {
            if (! self::resendIsAvailable()) {
                self::applyFromAddress($mail);

                return;
            }

            $apiKey = trim((string) ($mail['resend_api_key'] ?? ''));

            if ($apiKey !== '') {
                config([
                    'mail.default' => 'resend',
                    'services.resend.key' => $apiKey,
                ]);
            }

            self::applyFromAddress($mail);

            return;
        }

        if ($driver === self::DRIVER_SMTP) {
            $encryption = (string) ($mail['smtp_encryption'] ?? 'tls');

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => (string) ($mail['smtp_host'] ?? ''),
                'mail.mailers.smtp.port' => (int) ($mail['smtp_port'] ?? 587),
                'mail.mailers.smtp.username' => (string) ($mail['smtp_username'] ?? ''),
                'mail.mailers.smtp.password' => (string) ($mail['smtp_password'] ?? ''),
                'mail.mailers.smtp.scheme' => self::smtpScheme($encryption),
            ]);

            self::applyFromAddress($mail);

            return;
        }

        if ($driver === self::DRIVER_SENDMAIL) {
            config(['mail.default' => 'sendmail']);
            self::applyFromAddress($mail);
        }
    }

    /**
     * @param  array<string, mixed>  $mail
     */
    private static function applyFromAddress(array $mail): void
    {
        $fromAddress = trim((string) ($mail['from_address'] ?? ''));

        if ($fromAddress !== '') {
            config(['mail.from.address' => $fromAddress]);
        }

        $fromName = trim((string) ($mail['from_name'] ?? ''));

        if ($fromName !== '') {
            config(['mail.from.name' => $fromName]);
        }
    }

    private static function smtpScheme(string $encryption): ?string
    {
        return match ($encryption) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            'none', '' => null,
            default => null,
        };
    }
}
