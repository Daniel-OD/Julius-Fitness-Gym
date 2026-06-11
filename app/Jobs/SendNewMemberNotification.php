<?php

namespace App\Jobs;

use App\Contracts\SettingsRepository;
use App\Mail\NewMemberNotificationMail;
use App\Models\Member;
use App\Models\Plan;
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
 * Send an email to the admin when a member selects a plan via the member portal.
 */
class SendNewMemberNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $memberId,
        public readonly int $planId,
    ) {}

    public function handle(SettingsRepository $settingsRepository): void
    {
        $member = Member::find($this->memberId);
        $plan = Plan::find($this->planId);

        if (! $member || ! $plan) {
            return;
        }

        $settings = $settingsRepository->get();
        $adminEmail = Data::string(data_get($settings, 'general.admin_email'))
            ?: Data::string(data_get($settings, 'general.gym_email'));

        if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info('Skipping new member notification: admin email not configured.', [
                'member_id' => $this->memberId,
            ]);

            return;
        }

        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name')));

        Mail::to($adminEmail)->send(new NewMemberNotificationMail(
            memberName: Data::string($member->name),
            memberEmail: Data::string($member->email),
            memberPhone: Data::string($member->contact),
            planName: Data::string($plan->name),
            gymName: $gymName ?: 'Julius Fitness Gym',
        ));
    }
}
