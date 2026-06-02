<?php

namespace App\Services\Members;

use App\Support\Members\MemberImportDataset;
use InvalidArgumentException;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class MemberImportSpreadsheetReader
{
    public const MAX_FILE_BYTES = 10 * 1024 * 1024;

    public const MAX_ROWS = 5000;

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return ['csv', 'xlsx', 'xls'];
    }

    public function read(string $path, string $extension): MemberImportDataset
    {
        $extension = strtolower($extension);

        if ($extension === 'xls') {
            throw new InvalidArgumentException(__('app.settings.import.errors.xls_unsupported'));
        }

        if (! in_array($extension, ['csv', 'xlsx'], true)) {
            throw new InvalidArgumentException(__('app.settings.import.errors.invalid_format'));
        }

        $reader = $this->createReader($extension);
        $reader->open($path);

        try {
            $rawRows = [];

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = [];
                    foreach ($row->getCells() as $cell) {
                        $value = $cell->getValue();
                        $cells[] = is_string($value) || is_numeric($value)
                            ? trim((string) $value)
                            : '';
                    }

                    if ($this->rowHasContent($cells)) {
                        $rawRows[] = $cells;
                    }

                    if (count($rawRows) > self::MAX_ROWS) {
                        throw new InvalidArgumentException(__('app.settings.import.errors.too_many_rows', [
                            'max' => self::MAX_ROWS,
                        ]));
                    }
                }

                break;
            }
        } finally {
            $reader->close();
        }

        if ($rawRows === []) {
            throw new InvalidArgumentException(__('app.settings.import.errors.empty_file'));
        }

        $columnCount = max(array_map(count(...), $rawRows));
        $headers = [];
        $dataRows = $rawRows;

        for ($index = 0; $index < $columnCount; $index++) {
            $headers[] = __('app.settings.import.default_column', ['number' => $index + 1]);
        }

        return new MemberImportDataset($headers, $rawRows);
    }

    /**
     * @return list<string>
     */
    public function headersFromFirstRow(MemberImportDataset $dataset, bool $hasHeader): array
    {
        if (! $hasHeader || $dataset->rows === []) {
            return $dataset->headers;
        }

        $first = $dataset->rows[0];
        $headers = [];

        foreach ($dataset->headers as $index => $fallback) {
            $headers[] = filled($first[$index] ?? null)
                ? (string) $first[$index]
                : $fallback;
        }

        return $headers;
    }

    /**
     * @return list<list<string|null>>
     */
    public function dataRows(MemberImportDataset $dataset, bool $hasHeader): array
    {
        if (! $hasHeader) {
            return $dataset->rows;
        }

        return array_slice($dataset->rows, 1);
    }

    private function createReader(string $extension): ReaderInterface
    {
        if ($extension === 'csv') {
            $options = new CsvOptions;
            $options->ENCODING = 'UTF-8';

            return new CsvReader($options);
        }

        return new XlsxReader;
    }

    /**
     * @param  list<string>  $cells
     */
    private function rowHasContent(array $cells): bool
    {
        foreach ($cells as $cell) {
            if (filled($cell)) {
                return true;
            }
        }

        return false;
    }
}
