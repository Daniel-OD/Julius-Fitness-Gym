<?php

namespace App\Support\Members;

final readonly class MemberImportResult
{
    /**
     * @param  list<array{row_number: int, message: string, email: string|null, name: string|null}>  $errors
     */
    public function __construct(
        public int $imported,
        public int $skippedDuplicates,
        public int $updated,
        public int $failed,
        public int $subscriptionsCreated,
        public array $errors,
        public ?string $errorReportPath = null,
    ) {}
}
