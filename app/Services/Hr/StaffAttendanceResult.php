<?php

namespace App\Services\Hr;

use App\Models\StaffProfile;
use App\Models\User;

class StaffAttendanceResult
{
    public function __construct(
        public readonly string $result,
        public readonly string $message,
        public readonly int $httpStatus,
        public readonly ?User $user = null,
        public readonly ?StaffProfile $profile = null,
    ) {}

    public function color(): string
    {
        return match ($this->result) {
            'success', 'checkout_success' => 'success',
            'already_checked_in' => 'warning',
            default => 'danger',
        };
    }

    public function entryRecorded(): bool
    {
        return in_array($this->result, ['success', 'checkout_success', 'already_checked_in'], true);
    }
}
