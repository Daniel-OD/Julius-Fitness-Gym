<?php

namespace App\Support\Members;

final readonly class MemberImportAnalysis
{
    /**
     * @param  list<array{row_number: int, message: string}>  $rowErrors
     */
    public function __construct(
        public int $importableCount,
        public int $errorCount,
        public array $rowErrors,
    ) {}
}
