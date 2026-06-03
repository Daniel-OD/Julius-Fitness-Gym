<?php

namespace App\Support\Members;

final class MemberImportAnalysis
{
    /**
     * @param  list<array{row_number: int, message: string}>  $rowErrors
     */
    public function __construct(
        public readonly int $importableCount,
        public readonly int $errorCount,
        public readonly array $rowErrors,
    ) {}
}
