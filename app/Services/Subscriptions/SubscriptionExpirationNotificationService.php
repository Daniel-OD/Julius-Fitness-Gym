<?php

namespace App\Services\Subscriptions;

use App\Data\SubscriptionExpirationNotificationItem;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Helpers\Helpers;
use App\Models\Subscription;
use App\Models\SubscriptionExpirationNotificationRead;
use App\Models\User;
use App\Support\Subscriptions\ExpiringSubscriptionsQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SubscriptionExpirationNotificationService
{
    /**
     * @return Collection<int, Subscription>
     */
    private function expiringSoonSubscriptions(): Collection
    {
        return once(fn (): Collection => $this->expiringSoonQuery()->get());
    }

    /**
     * @return Builder<Subscription>
     */
    public function expiringSoonQuery(): Builder
    {
        return ExpiringSubscriptionsQuery::dueWithin(Helpers::getSubscriptionExpiringDays())
            ->with(['member', 'plan'])
            ->orderBy('end_date');
    }

    public function getUnreadCount(User $user): int
    {
        return $this->getItemsForUser($user)
            ->where(fn (SubscriptionExpirationNotificationItem $item): bool => ! $item->isRead)
            ->count();
    }

    /**
     * @return Collection<int, SubscriptionExpirationNotificationItem>
     */
    public function getItemsForUser(User $user): Collection
    {
        $subscriptions = $this->expiringSoonSubscriptions();

        if ($subscriptions->isEmpty()) {
            return collect();
        }

        $readIds = SubscriptionExpirationNotificationRead::query()
            ->where('user_id', $user->id)
            ->whereIn('subscription_id', $subscriptions->pluck('id'))
            ->pluck('subscription_id')
            ->all();

        return $subscriptions->map(function (Subscription $subscription) use ($readIds): SubscriptionExpirationNotificationItem {
            $daysLeft = max(ExpiringSubscriptionsQuery::daysLeft($subscription), 0);
            $expiresToday = $daysLeft === 0;
            $urgency = $this->resolveUrgency($daysLeft, $expiresToday);
            $member = $subscription->member;

            return new SubscriptionExpirationNotificationItem(
                subscriptionId: $subscription->id,
                memberName: (string) ($member?->name ?? __('app.fields.member')),
                memberPhotoUrl: $member?->photo
                    ? Storage::disk('public')->url($member->photo)
                    : null,
                memberInitials: $this->memberInitials($member?->name),
                planName: (string) ($subscription->plan?->name ?? '—'),
                daysLeft: $daysLeft,
                expiresToday: $expiresToday,
                urgency: $urgency,
                daysLabel: $this->formatDayCount($daysLeft),
                urgencyLabel: $this->urgencyLabel($urgency),
                url: SubscriptionResource::getUrl('view', ['record' => $subscription]),
                isRead: in_array($subscription->id, $readIds, true),
            );
        });
    }

    public function markAllAsRead(User $user): void
    {
        $now = now();

        foreach ($this->expiringSoonSubscriptions()->pluck('id') as $subscriptionId) {
            SubscriptionExpirationNotificationRead::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId,
                ],
                ['read_at' => $now],
            );
        }
    }

    public function markAsRead(User $user, int $subscriptionId): void
    {
        SubscriptionExpirationNotificationRead::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'subscription_id' => $subscriptionId,
            ],
            ['read_at' => now()],
        );
    }

    private function resolveUrgency(int $daysLeft, bool $expiresToday): string
    {
        if ($expiresToday || $daysLeft <= 1) {
            return 'critical';
        }

        if ($daysLeft <= 3) {
            return 'danger';
        }

        return 'warning';
    }

    private function urgencyLabel(string $urgency): string
    {
        return match ($urgency) {
            'critical' => __('app.notifications.expiry_urgency_critical'),
            'danger' => __('app.notifications.expiry_urgency_danger'),
            default => __('app.notifications.expiry_urgency_warning'),
        };
    }

    private function formatDayCount(int $days): string
    {
        $unit = $days === 1 ? __('app.units.day') : __('app.units.days');

        return "{$days} {$unit}";
    }

    private function memberInitials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim($name ?? '')) ?: [];
        $letters = collect($parts)
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)));

        return $letters->isNotEmpty() ? $letters->implode('') : '?';
    }
}
