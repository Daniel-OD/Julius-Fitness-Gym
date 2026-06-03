<?php

namespace App\Support\Members;

final class MemberImportResult
{
    /**
     * @param  list<array{row_number: int, message: string, email: string|null, name: string|null}>  $errors
     */
    public function __construct(
        public readonly int $imported,
        public readonly int $skippedDuplicates,
        public readonly int $updated,
        public readonly int $failed,
        public readonly array $errors,
        public readonly ?string $errorReportPath = null,
    ) {}
}
