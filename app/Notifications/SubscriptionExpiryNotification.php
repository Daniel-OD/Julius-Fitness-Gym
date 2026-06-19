<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Support\Data;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Filament database notification dispatched to all admin users when a subscription
 * is approaching expiry (7 / 3 / 1 days before) or has expired (day 0).
 */
class SubscriptionExpiryNotification extends Notification
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
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        $memberName = Data::string($this->subscription->member?->name);
        $planName = Data::string($this->subscription->plan?->name);
        $endDate = $this->subscription->end_date->translatedFormat('d M Y');

        $title = $this->daysLeft === 0
            ? __('app.notifications.subscription_expired_title', ['member' => $memberName])
            : __('app.notifications.subscription_expiring_title', ['days' => $this->daysLeft]);

        $body = __('app.notifications.subscription_expiring_body', [
            'member' => $memberName,
            'plan' => $planName,
            'date' => $endDate,
        ]);

        return FilamentNotification::make()
            ->title(Data::string($title))
            ->body(Data::string($body))
            ->warning()
            ->getDatabaseMessage();
    }
}
