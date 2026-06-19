<?php

namespace App\Jobs;

use App\Contracts\SettingsRepository;
use App\Mail\SubscriptionExpiringMail;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiryNotification;
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
 * Send in-app and (optionally) email notifications for a subscription
 * approaching expiry.
 *
 * Triggered by SubscriptionExpiryNotifications command.
 */
class SendSubscriptionExpiryNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $subscriptionId,
        public readonly int $daysLeft,
    ) {}

    public function handle(SettingsRepository $settingsRepository): void
    {
        $subscription = Subscription::with(['member', 'plan'])->find($this->subscriptionId);

        if (! $subscription) {
            return;
        }

        // In-app notification → all admin users
        $users = User::all();
        if ($users->isNotEmpty()) {
            $users->each->notify(new SubscriptionExpiryNotification($subscription, $this->daysLeft));
        }

        // Email notification → member (if enabled in settings)
        $settings = $settingsRepository->get();
        $emailEnabled = (bool) data_get($settings, 'notifications.email.enabled', false);
        $subscriptionExpiryEnabled = (bool) data_get($settings, 'notifications.email.subscription_expiring', false);

        if (! $emailEnabled || ! $subscriptionExpiryEnabled) {
            return;
        }

        $memberEmail = Data::string($subscription->member?->email);
        if (! filter_var($memberEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info('Skipping subscription expiry email: member email missing.', [
                'subscription_id' => $this->subscriptionId,
            ]);

            return;
        }

        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name')));
        $gymEmail = Data::string(data_get($settings, 'general.gym_email', ''));
        $gymContact = Data::string(data_get($settings, 'general.gym_contact', ''));
        $memberName = Data::string($subscription->member?->name);
        Data::string($subscription->plan?->name);

        $subject = $this->daysLeft === 0
            ? __('app.emails.subscription_expired_subject', ['gym' => $gymName])
            : __('app.emails.subscription_expiring_subject', ['days' => $this->daysLeft, 'gym' => $gymName]);

        $mailable = new SubscriptionExpiringMail(
            subscription: $subscription,
            subjectLine: Data::string($subject),
            gymName: $gymName ?: 'Julius Fitness Gym',
            gymEmail: $gymEmail,
            gymContact: $gymContact,
            memberName: $memberName,
            daysLeft: $this->daysLeft,
        );

        if (filled($gymEmail)) {
            $mailable->replyTo($gymEmail, $gymName);
        }

        Mail::to($memberEmail)->send($mailable);
    }
}
