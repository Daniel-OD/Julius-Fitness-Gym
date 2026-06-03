<?php

namespace App\Services\Members;

use App\Enums\MemberImportField;
use App\Enums\Status;
use App\Models\Member;
use App\Support\Members\MemberImportAnalysis;
use App\Support\Members\MemberImportDataset;
use App\Support\Members\MemberImportResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Writer;

class MemberImportService
{
    public const CHUNK_SIZE = 25;

    public function __construct(
        private readonly MemberImportSpreadsheetReader $reader,
        private readonly MemberImportColumnMapper $mapper,
    ) {}

    /**
     * @param  array<int, string>  $columnMapping
     * @return list<array<string, mixed>>
     */
    public function buildMappedRows(
        MemberImportDataset $dataset,
        bool $hasHeader,
        array $columnMapping,
    ): array {
        $headers = $this->reader->headersFromFirstRow($dataset, $hasHeader);
        $rows = $this->reader->dataRows($dataset, $hasHeader);
        $mapped = [];

        foreach ($rows as $offset => $row) {
            $record = [
                'row_number' => $hasHeader ? $offset + 2 : $offset + 1,
                'first_name' => null,
                'last_name' => null,
                'name' => null,
                'email' => null,
                'contact' => null,
                'dob' => null,
                'status' => null,
                'notes' => null,
            ];

            foreach ($columnMapping as $columnIndex => $field) {
                if ($field === MemberImportField::Ignore->value) {
                    continue;
                }

                $value = trim((string) ($row[$columnIndex] ?? ''));
                if ($value === '') {
                    continue;
                }

                $record[$field] = $value;
            }

            $record['name'] = $this->resolveName($record);
            $mapped[] = $record;
        }

        return $mapped;
    }

    /**
     * @param  list<array<string, mixed>>  $mappedRows
     */
    public function analyze(array $mappedRows): MemberImportAnalysis
    {
        $importable = 0;
        $errors = [];

        foreach ($mappedRows as $row) {
            $message = $this->validateRow($row);

            if ($message !== null) {
                $errors[] = [
                    'row_number' => (int) $row['row_number'],
                    'message' => $message,
                ];

                continue;
            }

            $importable++;
        }

        return new MemberImportAnalysis(
            importableCount: $importable,
            errorCount: count($errors),
            rowErrors: $errors,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $mappedRows
     */
    public function importChunk(
        array $mappedRows,
        int $offset,
        string $duplicateAction,
    ): MemberImportResult {
        $slice = array_slice($mappedRows, $offset, self::CHUNK_SIZE);
        $imported = 0;
        $skippedDuplicates = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($slice as $row) {
            $validation = $this->validateRow($row);

            if ($validation !== null) {
                $failed++;
                $errors[] = $this->errorPayload($row, $validation);

                continue;
            }

            $attributes = $this->attributesFromRow($row);
            $email = $attributes['email'] ?? null;

            if (filled($email)) {
                $existing = Member::query()->where('email', $email)->first();

                if ($existing !== null) {
                    if ($duplicateAction === 'update') {
                        $existing->update($attributes);
                        $updated++;
                    } else {
                        $skippedDuplicates++;
                    }

                    continue;
                }
            }

            try {
                Member::query()->create($attributes);
                $imported++;
            } catch (\Throwable $exception) {
                $failed++;
                $errors[] = $this->errorPayload($row, $exception->getMessage());
            }
        }

        return new MemberImportResult(
            imported: $imported,
            skippedDuplicates: $skippedDuplicates,
            updated: $updated,
            failed: $failed,
            errors: $errors,
        );
    }

    /**
     * @param  list<array{row_number: int, message: string, email: string|null, name: string|null}>  $errors
     */
    public function storeErrorReport(array $errors): ?string
    {
        if ($errors === []) {
            return null;
        }

        $uuid = (string) Str::uuid();
        $path = "member-import-reports/{$uuid}.csv";

        $writer = Writer::createFromString();
        $writer->insertOne([
            __('app.settings.import.report.row'),
            __('app.settings.import.report.name'),
            __('app.settings.import.report.email'),
            __('app.settings.import.report.error'),
        ]);

        foreach ($errors as $error) {
            $writer->insertOne([
                (string) $error['row_number'],
                (string) ($error['name'] ?? ''),
                (string) ($error['email'] ?? ''),
                (string) $error['message'],
            ]);
        }

        Storage::disk('local')->put($path, $writer->toString());

        return $path;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function attributesFromRow(array $row): array
    {
        $status = $this->parseStatus($row['status'] ?? null);

        return array_filter([
            'name' => $row['name'] ?? null,
            'email' => filled($row['email'] ?? null) ? Str::lower((string) $row['email']) : null,
            'contact' => $row['contact'] ?? null,
            'dob' => $this->parseDate($row['dob'] ?? null),
            'status' => $status,
            'health_issue' => $row['notes'] ?? null,
            'gender' => 'other',
            'source' => 'others',
            'goal' => 'fitness',
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveName(array $row): ?string
    {
        if (filled($row['name'] ?? null)) {
            return trim((string) $row['name']);
        }

        $parts = array_filter([
            $row['first_name'] ?? null,
            $row['last_name'] ?? null,
        ], fn (mixed $part): bool => filled($part));

        if ($parts === []) {
            return null;
        }

        return trim(implode(' ', $parts));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function validateRow(array $row): ?string
    {
        $name = $this->resolveName($row);
        $email = filled($row['email'] ?? null) ? Str::lower((string) $row['email']) : null;

        if (! filled($name) && ! filled($email)) {
            return __('app.settings.import.errors.missing_identifier');
        }

        if (filled($email) && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return __('app.settings.import.errors.invalid_email');
        }

        if (filled($row['dob'] ?? null) && $this->parseDate((string) $row['dob']) === null) {
            return __('app.settings.import.errors.invalid_dob');
        }

        return null;
    }

    private function parseStatus(?string $value): Status
    {
        if (! filled($value)) {
            return Status::Active;
        }

        $normalized = Str::of($value)->lower()->ascii()->toString();

        return match (true) {
            in_array($normalized, ['inactive', 'inactiv', '0', 'nu', 'no'], true) => Status::Inactive,
            default => Status::Active,
        };
    }

    private function parseDate(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'm/d/Y'];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, trim($value));

                if ($parsed !== false) {
                    return $parsed->toDateString();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{row_number: int, message: string, email: string|null, name: string|null}
     */
    private function errorPayload(array $row, string $message): array
    {
        return [
            'row_number' => (int) $row['row_number'],
            'message' => $message,
            'email' => filled($row['email'] ?? null) ? (string) $row['email'] : null,
            'name' => $this->resolveName($row),
        ];
    }
}
