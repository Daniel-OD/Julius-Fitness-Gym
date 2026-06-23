<?php

namespace App\Services\Members;

use App\Enums\MemberImportField;
use App\Enums\Status;
use App\Models\Member;
use App\Support\Members\MemberImportAnalysis;
use App\Support\Members\MemberImportDataset;
use App\Support\Members\MemberImportResult;
use App\Support\Members\MemberImportValueParser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Writer;

class MemberImportService
{
    public const CHUNK_SIZE = 25;

    public function __construct(
        private readonly MemberImportSpreadsheetReader $reader,
        private readonly MemberImportValueParser $valueParser,
        private readonly MemberImportSubscriptionProvisioner $subscriptionProvisioner,
        private readonly MemberStatusSyncService $statusSync,
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
        $this->reader->headersFromFirstRow($dataset, $hasHeader);
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
                'plan_name' => null,
                'plan_amount' => null,
                'plan_days' => null,
                'subscription_start' => null,
                'subscription_end' => null,
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
        $subscriptionsCreated = 0;
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
            $member = null;
            $memberHandled = false;

            if (filled($email)) {
                $existing = Member::query()->where('email', $email)->first();

                if ($existing !== null) {
                    $member = $existing;
                    $memberHandled = true;

                    if ($duplicateAction === 'update') {
                        $existing->update($attributes);
                        $updated++;
                    } else {
                        $skippedDuplicates++;
                    }
                }
            }

            if (! $memberHandled) {
                try {
                    $member = Member::query()->create($attributes);
                    $imported++;
                } catch (\Throwable $exception) {
                    $failed++;
                    $errors[] = $this->errorPayload($row, $exception->getMessage());

                    continue;
                }
            }

            if ($this->subscriptionProvisioner->hasSubscriptionData($row)) {
                try {
                    if ($this->subscriptionProvisioner->provision($member, $row)) {
                        $subscriptionsCreated++;
                    }

                    $this->statusSync->syncMember($member);
                } catch (\Throwable $exception) {
                    $failed++;
                    $errors[] = $this->errorPayload($row, $exception->getMessage());
                }
            }
        }

        return new MemberImportResult(
            imported: $imported,
            skippedDuplicates: $skippedDuplicates,
            updated: $updated,
            failed: $failed,
            subscriptionsCreated: $subscriptionsCreated,
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
            'dob' => $this->valueParser->parseDate($row['dob'] ?? null),
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
        ], filled(...));

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
        $email = $this->normalizeEmail($row);
        $error = $this->checkMissingIdentifier($name, $email);

        if (! $error) {
            $error = $this->checkInvalidEmail($email);
        }

        if (! $error) {
            $error = $this->checkInvalidDob($row);
        }

        if (! $error && $this->subscriptionProvisioner->hasSubscriptionData($row)) {
            $planName = $this->normalizePlanName($row);
            $amount = $this->valueParser->parseAmount($row['plan_amount'] ?? null);
            $error = $this->checkPlanIdentifier($planName, $amount);
        }

        return $error;
    }

    private function normalizeEmail(array $row): ?string
    {
        return filled($row['email'] ?? null)
            ? Str::lower((string) $row['email'])
            : null;
    }

    private function checkMissingIdentifier(?string $name, ?string $email): ?string
    {
        if (! filled($name) && ! filled($email)) {
            return __('app.settings.import.errors.missing_identifier');
        }

        return null;
    }

    private function checkInvalidEmail(?string $email): ?string
    {
        if (filled($email) && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return __('app.settings.import.errors.invalid_email');
        }

        return null;
    }

    private function checkInvalidDob(array $row): ?string
    {
        if (filled($row['dob'] ?? null)
            && $this->valueParser->parseDate((string) $row['dob']) === null
        ) {
            return __('app.settings.import.errors.invalid_dob');
        }

        return null;
    }

    private function normalizePlanName(array $row): ?string
    {
        return filled($row['plan_name'] ?? null)
            ? trim((string) $row['plan_name'])
            : null;
    }

    private function checkPlanIdentifier(?string $planName, $amount): ?string
    {
        if ($planName === null && $amount === null) {
            return __('app.settings.import.errors.missing_plan_identifier');
        }

        if (filled($row['plan_amount'] ?? null) && $amount === null) {
            return __('app.settings.import.errors.invalid_plan_amount');
        }

        if (filled($row['plan_days'] ?? null) && $this->valueParser->parseDays((string) $row['plan_days']) === null) {
            return __('app.settings.import.errors.invalid_plan_days');
        }

        if (filled($row['subscription_start'] ?? null) && $this->valueParser->parseDate((string) $row['subscription_start']) === null) {
            return __('app.settings.import.errors.invalid_subscription_start');
        }

        if (filled($row['subscription_end'] ?? null) && $this->valueParser->parseDate((string) $row['subscription_end']) === null) {
            return __('app.settings.import.errors.invalid_subscription_end');
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
