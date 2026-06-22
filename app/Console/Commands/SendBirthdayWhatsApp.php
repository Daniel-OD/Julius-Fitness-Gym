<?php

namespace App\Console\Commands;

use App\Contracts\SettingsRepository;
use App\Jobs\SendWhatsAppBirthday;
use App\Models\Member;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

#[Description('Send WhatsApp birthday greetings to members whose birthday is today')]
#[Signature('gym:birthday-whatsapp')]
class SendBirthdayWhatsApp extends Command
{
    public function handle(SettingsRepository $settingsRepository): int
    {
        $settings = $settingsRepository->get();
        $whatsAppEnabled = (bool) data_get($settings, 'notifications.whatsapp.enabled', false);

        if (! $whatsAppEnabled) {
            $this->info('WhatsApp notifications are disabled. Skipping birthday messages.');

            return self::SUCCESS;
        }

        $today = Carbon::today();

        $members = Member::query()
            ->whereNotNull('contact')
            ->whereNotNull('dob')
            ->whereMonth('dob', $today->month)
            ->whereDay('dob', $today->day)
            ->get();

        $dispatched = 0;

        foreach ($members as $member) {
            SendWhatsAppBirthday::dispatch($member->id);
            $dispatched++;
        }

        $this->info("Dispatched {$dispatched} birthday WhatsApp job(s).");

        Log::info('gym:birthday-whatsapp finished', ['dispatched' => $dispatched]);

        return self::SUCCESS;
    }
}
