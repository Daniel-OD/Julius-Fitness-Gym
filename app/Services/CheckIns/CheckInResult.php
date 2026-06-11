<?php

namespace App\Services\CheckIns;

use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Subscription;

/**
 * Outcome of a QR scan attempt, shared by the public check-in endpoint
 * and the reception scan panel.
 */
final readonly class CheckInResult
{
    public function __construct(
        public string $result,
        public string $message,
        public int $httpStatus,
        public ?Member $member = null,
        public ?Subscription $subscription = null,
        public ?CheckIn $checkIn = null,
    ) {}

    /**
     * Traffic-light color for scanner UIs: green = entry, yellow = entry
     * with warning (or transient state), red = no entry.
     */
    public function color(): string
    {
        return match ($this->result) {
            'success' => 'green',
            'grace_entry', 'already_present', 'rate_limited' => 'yellow',
            default => 'red',
        };
    }

    public function entryRecorded(): bool
    {
        return in_array($this->result, ['success', 'grace_entry'], true);
    }
}
