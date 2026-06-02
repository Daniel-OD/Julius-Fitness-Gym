<?php

namespace App\Data;

readonly class SubscriptionExpirationNotificationItem
{
    public function __construct(
        public int $subscriptionId,
        public string $memberName,
        public ?string $memberPhotoUrl,
        public string $memberInitials,
        public string $planName,
        public int $daysLeft,
        public bool $expiresToday,
        public string $urgency,
        public string $daysLabel,
        public string $urgencyLabel,
        public string $url,
        public bool $isRead,
    ) {}
}
