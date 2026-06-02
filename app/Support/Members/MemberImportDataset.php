<?php

namespace App\Support\Members;

/**
 * Parsed spreadsheet contents for member import.
 */
final class MemberImportDataset
{
    /**
     * @param  list<string>  $headers  Column labels (from file or generated).
     * @param  list<list<string|null>>  $rows  Data rows (excluding header row when applicable).
     */
    public function __construct(
        public readonly array $headers,
        public readonly array $rows,
    ) {}

    public function columnCount(): int
    {
        return count($this->headers);
    }
}
