<?php

namespace App\Jobs;

use App\Contracts\SettingsRepository;
use App\Mail\GraceEntryNotificationMail;
use App\Models\CheckIn;
use App\Models\Subscription;
use App\Support\AppConfig;
use App\Support\Data;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Notify the admin when an expired member uses their one-time grace entry.
 */
class SendGraceEntryNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $checkInId,
    ) {}

    public function handle(SettingsRepository $settingsRepository): void
    {
        $checkIn = CheckIn::with('member')->find($this->checkInId);
        $member = $checkIn?->member;

        if (! $checkIn || ! $member) {
            return;
        }

        $settings = $settingsRepository->get();
        $adminEmail = Data::string(data_get($settings, 'general.admin_email'))
            ?: Data::string(data_get($settings, 'general.gym_email'));

        if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info('Skipping grace entry notification: admin email not configured.', [
                'check_in_id' => $this->checkInId,
            ]);

            return;
        }

        $expiredSubscription = Subscription::query()
            ->with('plan')
            ->where('member_id', $member->id)
            ->whereNotIn('status', ['cancelled', 'renewed'])
            ->latest('end_date')
            ->first();

        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name')));
        $timezone = AppConfig::timezone();

        Mail::to($adminEmail)->send(new GraceEntryNotificationMail(
            memberName: Data::string($member->name),
            memberCode: Data::string($member->code),
            planName: Data::string($expiredSubscription?->plan?->name),
            expiredOn: $expiredSubscription?->end_date?->translatedFormat('d M Y') ?? '—',
            scannedAt: $checkIn->checked_in_at->timezone($timezone)->translatedFormat('d M Y, H:i'),
            gymName: $gymName ?: 'Julius Fitness Gym',
        ));
    }
}
