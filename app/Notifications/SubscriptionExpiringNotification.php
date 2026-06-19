<?php

namespace App\Notifications;

use App\Helpers\Helpers;
use App\Models\Member;
use App\Models\Subscription;
use App\Support\AppConfig;
use App\Support\Data;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Member-facing email notification when a subscription is expiring soon.
 */
class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Subscription $subscription,
        public readonly int $daysLeft,
    ) {}

    /**
     * @return list<string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Member $notifiable): MailMessage
    {
        $this->subscription->loadMissing(['member', 'plan']);

        $settings = Helpers::getSettings();
        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name'))) ?: 'Julius Fitness Gym';
        $memberName = Data::string($this->subscription->member?->name ?: $notifiable->name);
        $planName = Data::string($this->subscription->plan?->name);
        $expiryDate = $this->subscription->end_date->translatedFormat('d M Y');

        return (new MailMessage)
            ->subject(__('notifications.subscription_expiring.subject', [
                'gym' => $gymName,
                'days' => $this->daysLeft,
            ]))
            ->greeting(__('notifications.subscription_expiring.greeting', [
                'name' => filled($memberName) ? $memberName : __('notifications.subscription_expiring.there'),
            ]))
            ->line(__('notifications.subscription_expiring.expiry_line', [
                'plan' => filled($planName) ? $planName : '—',
                'date' => $expiryDate,
                'days' => $this->daysLeft,
            ]))
            ->action(__('notifications.subscription_expiring.action'), url('/member/login'))
            ->line(__('notifications.subscription_expiring.footer'));
    }
}
