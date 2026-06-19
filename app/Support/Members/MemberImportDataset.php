<?php

namespace App\Support\Members;

/**
 * Parsed spreadsheet contents for member import.
 */
final readonly class MemberImportDataset
{
    /**
     * @param  list<string>  $headers  Column labels (from file or generated).
     * @param  list<list<string|null>>  $rows  Data rows (excluding header row when applicable).
     */
    public function __construct(
        public array $headers,
        public array $rows,
    ) {}

    public function columnCount(): int
    {
        return count($this->headers);
    }
}
