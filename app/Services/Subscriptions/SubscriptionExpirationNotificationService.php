<?php

namespace App\Services\Subscriptions;

use App\Data\SubscriptionExpirationNotificationItem;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Helpers\Helpers;
use App\Models\Subscription;
use App\Models\SubscriptionExpirationNotificationRead;
use App\Models\User;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SubscriptionExpirationNotificationService
{
    /**
     * @return Builder<Subscription>
     */
    public function expiringSoonQuery(): Builder
    {
        $today = CarbonImmutable::today(AppConfig::timezone());
        $end = $today->addDays(Helpers::getSubscriptionExpiringDays());

        return Subscription::query()
            ->with(['member', 'plan'])
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $today->toDateString())
            ->whereDate('end_date', '<=', $end->toDateString())
            ->orderBy('end_date');
    }

    public function getUnreadCount(User $user): int
    {
        $subscriptionIds = $this->expiringSoonQuery()->pluck('id');

        if ($subscriptionIds->isEmpty()) {
            return 0;
        }

        $readCount = SubscriptionExpirationNotificationRead::query()
            ->where('user_id', $user->id)
            ->whereIn('subscription_id', $subscriptionIds)
            ->count();

        return $subscriptionIds->count() - $readCount;
    }

    /**
     * @return Collection<int, SubscriptionExpirationNotificationItem>
     */
    public function getItemsForUser(User $user): Collection
    {
        $subscriptions = $this->expiringSoonQuery()->get();

        if ($subscriptions->isEmpty()) {
            return collect();
        }

        $readIds = SubscriptionExpirationNotificationRead::query()
            ->where('user_id', $user->id)
            ->whereIn('subscription_id', $subscriptions->pluck('id'))
            ->pluck('subscription_id')
            ->all();

        $today = CarbonImmutable::today(AppConfig::timezone());

        return $subscriptions->map(function (Subscription $subscription) use ($readIds, $today): SubscriptionExpirationNotificationItem {
            $endDate = CarbonImmutable::parse($subscription->end_date, AppConfig::timezone())->startOfDay();
            $daysLeft = (int) max($today->diffInDays($endDate, false), 0);
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

        foreach ($this->expiringSoonQuery()->pluck('id') as $subscriptionId) {
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
