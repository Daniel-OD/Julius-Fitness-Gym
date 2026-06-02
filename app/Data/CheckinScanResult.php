<?php

namespace App\Data;

use App\Enums\CheckinScanStatus;
use Illuminate\Support\Carbon;

final class CheckinScanResult
{
    public function __construct(
        public CheckinScanStatus $status,
        public ?string $memberName = null,
        public ?Carbon $checkedInAt = null,
        public ?string $message = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->status === CheckinScanStatus::Success;
    }
}
