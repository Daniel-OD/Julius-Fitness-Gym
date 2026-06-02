<?php

namespace App\Filament\Livewire;

use App\Services\Subscriptions\SubscriptionExpirationNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SubscriptionExpirationNotifications extends Component
{
    public int $unreadCount = 0;

    /**
     * @var list<array<string, mixed>>
     */
    public array $notifications = [];

    public function mount(SubscriptionExpirationNotificationService $service): void
    {
        $this->refreshNotifications($service);
    }

    public function markAllAsRead(SubscriptionExpirationNotificationService $service): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $service->markAllAsRead($user);
        $this->refreshNotifications($service);
    }

    public function markAsRead(int $subscriptionId, SubscriptionExpirationNotificationService $service): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $service->markAsRead($user, $subscriptionId);
        $this->refreshNotifications($service);
    }

    private function refreshNotifications(SubscriptionExpirationNotificationService $service): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->notifications = [];
            $this->unreadCount = 0;

            return;
        }

        $this->notifications = $service->getItemsForUser($user)
            ->map(fn ($item): array => [
                'subscriptionId' => $item->subscriptionId,
                'memberName' => $item->memberName,
                'memberPhotoUrl' => $item->memberPhotoUrl,
                'memberInitials' => $item->memberInitials,
                'planName' => $item->planName,
                'daysLeft' => $item->daysLeft,
                'expiresToday' => $item->expiresToday,
                'urgency' => $item->urgency,
                'daysLabel' => $item->daysLabel,
                'urgencyLabel' => $item->urgencyLabel,
                'url' => $item->url,
                'isRead' => $item->isRead,
            ])
            ->values()
            ->all();

        $this->unreadCount = $service->getUnreadCount($user);
    }

    public function render(): View
    {
        return view('filament.components.subscription-expiration-notifications');
    }
}
