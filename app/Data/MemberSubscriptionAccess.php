<?php

namespace App\Data;

final class MemberSubscriptionAccess
{
    public function __construct(
        public bool $isActive,
        public ?int $daysRemaining,
        public string $label,
        public string $tone,
    ) {}
}
