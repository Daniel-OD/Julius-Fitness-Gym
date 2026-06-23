<?php

use App\Enums\MemberImportField;
use App\Services\Members\MemberImportColumnMapper;
use App\Services\Members\MemberImportService;
use App\Services\Members\MemberImportSpreadsheetReader;
use App\Support\Members\MemberImportDataset;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public $importFile = null;

    public ?string $storedFilePath = null;

    public ?string $storedExtension = null;

    public bool $hasHeader = true;

    /** @var array<int, string> */
    public array $columnMapping = [];

    /** @var array<int, string> */
    public array $autoDetected = [];

    public string $duplicateAction = 'skip';

    public bool $importing = false;

    public bool $importRunStarted = false;

    public int $importProgress = 0;

    public int $importTotal = 0;

    public int $importedCount = 0;

    public int $skippedCount = 0;

    public int $updatedCount = 0;

    public int $subscriptionsCreatedCount = 0;

    public int $failedCount = 0;

    public ?string $errorReportToken = null;

    public ?string $importToken = null;

    public ?int $summaryImportable = null;

    public ?int $summaryErrors = null;

    public function updatedImportFile(): void
    {
        $this->resetImportState(keepFile: false);

        try {
            $this->validate([
                'importFile' => ['required', 'file', 'extensions:csv,xlsx,xls', 'max:10240'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->importFile = null;

            throw $exception;
        }

        $extension = strtolower($this->importFile->getClientOriginalExtension());
        $path = $this->importFile->storeAs(
            'member-imports/'.auth()->id(),
            Str::uuid().'.'.$extension,
            'local',
        );

        if ($path === false) {
            $this->importFile = null;
            $this->addError('importFile', __('app.settings.import.errors.storage_failed'));

            return;
        }

        $this->storedFilePath = $path;
        $this->storedExtension = $extension;
        $this->importFile = null;

        try {
            $dataset = $this->spreadsheetReader()->read(
                Storage::disk('local')->path($path),
                $extension,
            );
            $headers = $this->spreadsheetReader()->headersFromFirstRow($dataset, $this->hasHeader);
            $this->columnMapping = $this->columnMapper()->suggest($headers);
            $this->autoDetected = $this->columnMapper()->autoDetectedFields($headers);
        } catch (\Throwable $exception) {
            $this->clearStoredFile();
            $this->addError('importFile', $exception->getMessage());
        }
    }

    public function updatedHasHeader(): void
    {
        if ($this->storedFilePath === null) {
            return;
        }

        $headers = $this->spreadsheetReader()->headersFromFirstRow($this->dataset(), $this->hasHeader);
        $this->columnMapping = $this->columnMapper()->suggest($headers);
        $this->autoDetected = $this->columnMapper()->autoDetectedFields($headers);
    }

    public function continueToStep2(): void
    {
        if ($this->storedFilePath === null) {
            $this->addError('importFile', __('app.settings.import.errors.file_required'));

            return;
        }

        $this->step = 2;
    }

    public function continueToStep3(): void
    {
        if (! $this->hasRequiredMapping()) {
            $this->addError('columnMapping', __('app.settings.import.errors.mapping_required'));

            return;
        }

        try {
            $analysis = $this->importService()->analyze($this->mappedRows());
        } catch (\Throwable $exception) {
            $this->notifyFileUnavailable($exception);

            return;
        }

        $this->summaryImportable = $analysis->importableCount;
        $this->summaryErrors = $analysis->errorCount;
        $this->step = 3;
    }

    public function backToStep1(): void
    {
        $this->importFile = null;
        $this->resetImportState(keepFile: false);
        $this->step = 1;
    }

    public function backToStep2(): void
    {
        $this->step = 2;
        $this->resetImportProgress();
    }

    public function startImport(): bool
    {
        $this->beginImportRun();

        try {
            $rows = $this->mappedRows();
        } catch (\Throwable $exception) {
            $this->abortImportRun();
            $this->notifyFileUnavailable($exception);

            return false;
        }

        // Persist the parsed rows in the (database) cache so the chunked import no longer
        // depends on the uploaded file still being present on local disk between requests.
        $this->importToken = (string) Str::uuid();
        Cache::put($this->importCacheKey(), $rows, now()->addHour());

        $this->importTotal = count($rows);
        $this->importProgress = 0;

        return true;
    }

    public function cancelImport(): void
    {
        $this->abortImportRun();
    }

    public function importChunk(int $offset): array
    {
        $mappedRows = $this->importToken !== null
            ? Cache::get($this->importCacheKey())
            : null;

        if (! is_array($mappedRows)) {
            // Cache expired or the import was never started cleanly — fall back to the file.
            try {
                $mappedRows = $this->mappedRows();
            } catch (\Throwable $e) {
                $this->importing = false;
                $this->notifyFileUnavailable($e);

                return ['done' => true, 'progress' => 0, 'total' => 0];
            }
        }

        $result = $this->importService()->importChunk(
            $mappedRows,
            $offset,
            $this->duplicateAction,
        );

        $this->importedCount += $result->imported;
        $this->skippedCount += $result->skippedDuplicates;
        $this->updatedCount += $result->updated;
        $this->subscriptionsCreatedCount += $result->subscriptionsCreated;
        $this->failedCount += $result->failed;
        $this->importProgress = min($offset + MemberImportService::CHUNK_SIZE, $this->importTotal);

        $errors = session('member_import_errors', []);
        session(['member_import_errors' => array_merge($errors, $result->errors)]);

        $done = $offset + MemberImportService::CHUNK_SIZE >= $this->importTotal;

        if ($done) {
            $this->importing = false;
            $allErrors = session('member_import_errors', []);
            session()->forget('member_import_errors');

            if ($allErrors !== []) {
                $path = $this->importService()->storeErrorReport($allErrors);
                $token = (string) Str::uuid();
                session(["member_import_error_report.{$token}" => $path]);
                $this->errorReportToken = $token;
            }

            $this->clearImportCache();
            $this->clearStoredFile();
        }

        return [
            'done' => $done,
            'progress' => $this->importProgress,
            'total' => $this->importTotal,
        ];
    }

    public function removeFile(): void
    {
        $this->clearStoredFile();
        $this->importFile = null;
        $this->resetImportState(keepFile: false);
    }

    public function reportUploadError(?string $message = null): void
    {
        $this->importFile = null;
        $this->addError(
            'importFile',
            filled($message) ? $message : __('app.settings.import.errors.upload_failed'),
        );
    }

    #[Computed]
    public function fileHeaders(): array
    {
        if ($this->storedFilePath === null) {
            return [];
        }

        return $this->spreadsheetReader()->headersFromFirstRow($this->dataset(), $this->hasHeader);
    }

    #[Computed]
    public function filePreviewRows(): array
    {
        if ($this->storedFilePath === null) {
            return [];
        }

        return array_slice($this->spreadsheetReader()->dataRows($this->dataset(), $this->hasHeader), 0, 5);
    }

    #[Computed]
    public function mappingPreviewRows(): array
    {
        return array_slice($this->mappedRows(), 0, 3);
    }

    #[Computed]
    public function mappingFieldOptions(): array
    {
        return MemberImportField::options();
    }

    #[Computed]
    public function requiredFieldKeys(): array
    {
        return [
            MemberImportField::Email->value,
            MemberImportField::Name->value,
            MemberImportField::FirstName->value,
            MemberImportField::LastName->value,
        ];
    }

    #[Computed]
    public function importProgressPercent(): int
    {
        if ($this->importTotal === 0) {
            return 0;
        }

        return (int) round(($this->importProgress / $this->importTotal) * 100);
    }

    #[Computed]
    public function showImportConfirm(): bool
    {
        return ! $this->importRunStarted;
    }

    #[Computed]
    public function showImportProgress(): bool
    {
        if (! $this->importRunStarted) {
            return false;
        }

        return $this->importing || ($this->importTotal > 0 && $this->importProgress < $this->importTotal);
    }

    #[Computed]
    public function showImportResults(): bool
    {
        return $this->importRunStarted
            && ! $this->importing
            && $this->importTotal > 0
            && $this->importProgress >= $this->importTotal;
    }

    private function dataset(): MemberImportDataset
    {
        return $this->spreadsheetReader()->read(
            Storage::disk('local')->path((string) $this->storedFilePath),
            (string) $this->storedExtension,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mappedRows(): array
    {
        return $this->importService()->buildMappedRows(
            $this->dataset(),
            $this->hasHeader,
            $this->columnMapping,
        );
    }

    private function hasRequiredMapping(): bool
    {
        $mapped = array_values(array_filter(
            $this->columnMapping,
            fn (string $field): bool => $field !== MemberImportField::Ignore->value,
        ));

        $hasEmail = in_array(MemberImportField::Email->value, $mapped, true);
        $hasName = in_array(MemberImportField::Name->value, $mapped, true)
            || in_array(MemberImportField::FirstName->value, $mapped, true)
            || in_array(MemberImportField::LastName->value, $mapped, true);

        return $hasEmail || $hasName;
    }

    private function spreadsheetReader(): MemberImportSpreadsheetReader
    {
        return app(MemberImportSpreadsheetReader::class);
    }

    private function columnMapper(): MemberImportColumnMapper
    {
        return app(MemberImportColumnMapper::class);
    }

    private function importService(): MemberImportService
    {
        return app(MemberImportService::class);
    }

    private function clearStoredFile(): void
    {
        if ($this->storedFilePath !== null && Storage::disk('local')->exists($this->storedFilePath)) {
            Storage::disk('local')->delete($this->storedFilePath);
        }

        $this->storedFilePath = null;
        $this->storedExtension = null;
    }

    private function resetImportState(bool $keepFile = true): void
    {
        $this->step = 1;
        $this->columnMapping = [];
        $this->autoDetected = [];
        $this->summaryImportable = null;
        $this->summaryErrors = null;
        $this->resetImportProgress();

        if (! $keepFile) {
            $this->clearStoredFile();
        }
    }

    private function beginImportRun(): void
    {
        $this->importRunStarted = true;
        $this->importing = true;
        $this->importProgress = 0;
        $this->importTotal = 0;
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->updatedCount = 0;
        $this->subscriptionsCreatedCount = 0;
        $this->failedCount = 0;
        $this->errorReportToken = null;
        $this->clearImportCache();
    }

    private function abortImportRun(): void
    {
        $this->importRunStarted = false;
        $this->importing = false;
        $this->clearImportCache();
    }

    private function resetImportProgress(): void
    {
        $this->importRunStarted = false;
        $this->importing = false;
        $this->importProgress = 0;
        $this->importTotal = 0;
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->updatedCount = 0;
        $this->subscriptionsCreatedCount = 0;
        $this->failedCount = 0;
        $this->errorReportToken = null;
        $this->clearImportCache();
    }

    private function importCacheKey(): string
    {
        return 'member-import:rows:'.$this->importToken;
    }

    private function clearImportCache(): void
    {
        if ($this->importToken !== null) {
            Cache::forget($this->importCacheKey());
            $this->importToken = null;
        }
    }

    private function notifyFileUnavailable(\Throwable $exception): void
    {
        $this->importing = false;

        Notification::make()
            ->title(__('app.settings.import.errors.file_unavailable'))
            ->body($exception->getMessage())
            ->danger()
            ->persistent()
            ->send();
    }
};
?>

<div class="jf-import-wizard">
    {{-- Step indicator --}}
    <nav class="jf-import-steps" aria-label="{{ __('app.settings.import.steps_label') }}">
        @foreach ([1 => __('app.settings.import.step_upload'), 2 => __('app.settings.import.step_mapping'), 3 => __('app.settings.import.step_confirm')] as $number => $label)
            @php
                $state = match (true) {
                    $step > $number => 'complete',
                    $step === $number => 'active',
                    default => 'upcoming',
                };
            @endphp
            <div @class(['jf-import-step', "jf-import-step--{$state}"])>
                <span class="jf-import-step__number">{{ $number }}</span>
                <span class="jf-import-step__label">{{ $label }}</span>
            </div>
            @if (! $loop->last)
                <div @class(['jf-import-step__connector', 'jf-import-step__connector--complete' => $step > $number])></div>
            @endif
        @endforeach
    </nav>

    {{-- Step 1: Upload --}}
    @if ($step === 1)
        <div class="jf-import-panel">
            <label
                for="member-import-file"
                class="jf-import-dropzone @error('importFile') jf-import-dropzone--error @enderror"
                x-data="{ dragging: false }"
                x-on:dragover.prevent="dragging = true"
                x-on:dragleave.prevent="dragging = false"
                x-on:drop.prevent="
                    dragging = false;
                    const file = $event.dataTransfer?.files?.[0];
                    if (file) {
                        $wire.upload(
                            'importFile',
                            file,
                            () => {},
                            (errors) => {
                                const message = Array.isArray(errors) ? errors.join(' ') : String(errors ?? '');
                                $wire.reportUploadError(message);
                            },
                            () => {},
                        );
                    }
                "
                :class="{ 'jf-import-dropzone--active': dragging }"
            >
                <input
                    id="member-import-file"
                    type="file"
                    class="sr-only"
                    wire:model="importFile"
                    accept=".csv,.xlsx,.xls"
                />
                <div class="jf-import-dropzone__icons">
                    <x-filament::icon icon="heroicon-o-table-cells" class="jf-import-file-icon jf-import-file-icon--xlsx" />
                    <x-filament::icon icon="heroicon-o-document-text" class="jf-import-file-icon jf-import-file-icon--csv" />
                </div>
                <p class="jf-import-dropzone__title">{{ __('app.settings.import.drop_title') }}</p>
                <p class="jf-import-dropzone__hint">{{ __('app.settings.import.drop_hint') }}</p>
                <span wire:loading wire:target="importFile" class="jf-import-dropzone__loading">
                    {{ __('app.settings.import.parsing') }}
                </span>
            </label>

            @error('importFile')
                <p class="jf-import-error">{{ $message }}</p>
            @enderror

            <div class="jf-import-actions jf-import-actions--between">
                <a
                    href="{{ asset('templates/membri-template.xlsx') }}"
                    class="fi-btn fi-btn-size-md fi-color-gray fi-btn-outlined"
                    download="membri-template.xlsx"
                >
                    <x-filament::icon icon="heroicon-m-arrow-down-tray" class="fi-btn-icon" />
                    {{ __('app.settings.import.download_template') }}
                </a>

                @if ($storedFilePath)
                    <button type="button" wire:click="removeFile" class="fi-btn fi-btn-size-md fi-color-gray">
                        {{ __('app.settings.import.remove_file') }}
                    </button>
                @endif
            </div>

            @if (count($this->filePreviewRows) > 0)
                <div class="jf-import-preview">
                    <h3 class="jf-import-preview__title">{{ __('app.settings.import.preview_title') }}</h3>
                    <div class="jf-import-table-wrap">
                        <table class="jf-import-table">
                            <thead>
                                <tr>
                                    @foreach ($this->fileHeaders as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->filePreviewRows as $row)
                                    <tr>
                                        @foreach ($this->fileHeaders as $index => $header)
                                            <td>{{ $row[$index] ?? '' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="jf-import-actions jf-import-actions--end">
                <x-filament::button
                    color="primary"
                    wire:click="continueToStep2"
                    :disabled="! $storedFilePath"
                >
                    {{ __('app.settings.import.continue') }}
                </x-filament::button>
            </div>
        </div>
    @endif

    {{-- Step 2: Mapping --}}
    @if ($step === 2)
        <div class="jf-import-panel">
            <label class="jf-import-checkbox">
                <input type="checkbox" wire:model.live="hasHeader" />
                <span>{{ __('app.settings.import.first_row_headers') }}</span>
            </label>

            @error('columnMapping')
                <p class="jf-import-error">{{ $message }}</p>
            @enderror

            <p class="jf-import-hint">
                <span class="jf-import-required">*</span> {{ __('app.settings.import.required_hint') }}
            </p>

            <div class="jf-import-table-wrap">
                <table class="jf-import-mapping-table">
                    <thead>
                        <tr>
                            <th>{{ __('app.settings.import.column_source') }}</th>
                            <th>{{ __('app.settings.import.column_target') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->fileHeaders as $index => $header)
                            @php
                                $previewRow = $this->filePreviewRows[0] ?? [];
                                $isAuto = isset($autoDetected[$index]);
                            @endphp
                            <tr @class(['jf-import-mapping-row--auto' => $isAuto])>
                                <td>
                                    <div class="jf-import-mapping-source">
                                        <strong>{{ $header }}</strong>
                                        @if ($isAuto)
                                            <span class="jf-import-auto-badge">{{ __('app.settings.import.auto_detected') }}</span>
                                        @endif
                                        <div class="jf-import-mapping-samples">
                                            @foreach (array_slice($this->filePreviewRows, 0, 2) as $sampleRow)
                                                <span>{{ $sampleRow[$index] ?? '—' }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select
                                        wire:model.live="columnMapping.{{ $index }}"
                                        class="jf-select @if (in_array($columnMapping[$index] ?? '', $this->requiredFieldKeys, true)) jf-import-select--required @endif"
                                    >
                                        @foreach ($this->mappingFieldOptions as $value => $label)
                                            <option value="{{ $value }}">
                                                {{ $label }}
                                                @if (in_array($value, $this->requiredFieldKeys, true))
                                                    *
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (count($this->mappingPreviewRows) > 0)
                <div class="jf-import-preview">
                    <h3 class="jf-import-preview__title">{{ __('app.settings.import.mapped_preview_title') }}</h3>
                    <div class="jf-import-table-wrap">
                        <table class="jf-import-table">
                            <thead>
                                <tr>
                                    <th>{{ __('app.fields.name') }}</th>
                                    <th>{{ __('app.fields.email') }}</th>
                                    <th>{{ __('app.fields.contact') }}</th>
                                    <th>{{ __('app.fields.dob') }}</th>
                                    <th>{{ __('app.fields.status') }}</th>
                                    <th>{{ __('app.settings.import.fields.plan_name') }}</th>
                                    <th>{{ __('app.settings.import.fields.plan_amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->mappingPreviewRows as $row)
                                    <tr>
                                        <td>{{ $row['name'] ?? trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')) }}</td>
                                        <td>{{ $row['email'] ?? '—' }}</td>
                                        <td>{{ $row['contact'] ?? '—' }}</td>
                                        <td>{{ $row['dob'] ?? '—' }}</td>
                                        <td>{{ $row['status'] ?? '—' }}</td>
                                        <td>{{ $row['plan_name'] ?? '—' }}</td>
                                        <td>{{ $row['plan_amount'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="jf-import-actions jf-import-actions--between">
                <x-filament::button color="gray" wire:click="backToStep1">
                    {{ __('app.settings.import.back') }}
                </x-filament::button>
                <x-filament::button color="primary" wire:click="continueToStep3">
                    {{ __('app.settings.import.continue') }}
                </x-filament::button>
            </div>
        </div>
    @endif

    {{-- Step 3: Confirm & results --}}
    @if ($step === 3)
        <div
            class="jf-import-panel"
            x-data="{
                busy: false,
                chunkSize: {{ \App\Services\Members\MemberImportService::CHUNK_SIZE }},
                async runImport() {
                    if (this.busy) {
                        return;
                    }

                    this.busy = true;

                    try {
                        const started = await $wire.startImport();

                        if (! started) {
                            return;
                        }

                        let offset = 0;
                        let result;

                        do {
                            result = await $wire.importChunk(offset);
                            offset += this.chunkSize;

                            await new Promise((resolve) => {
                                requestAnimationFrame(() => requestAnimationFrame(resolve));
                            });
                        } while (result && ! result.done);
                    } catch (error) {
                        await $wire.cancelImport();
                    } finally {
                        this.busy = false;
                    }
                },
            }"
        >
            <div
                class="jf-import-progress-panel"
                x-show="busy && ! $wire.importRunStarted"
                x-cloak
            >
                <div class="jf-import-progress-status">
                    <span class="jf-import-spinner" aria-hidden="true"></span>
                    <p class="jf-import-progress__title">{{ __('app.settings.import.preparing') }}</p>
                </div>
                <div class="jf-import-progress jf-import-progress--indeterminate" aria-hidden="true">
                    <div class="jf-import-progress__bar jf-import-progress__bar--indeterminate"></div>
                </div>
            </div>

            @if ($this->showImportConfirm)
                <div class="jf-import-summary">
                    <p class="jf-import-summary__line">
                        {{ __('app.settings.import.summary_importable', ['count' => $summaryImportable ?? 0]) }}
                    </p>
                    <p class="jf-import-summary__line jf-import-summary__line--muted">
                        {{ __('app.settings.import.summary_errors', ['count' => $summaryErrors ?? 0]) }}
                    </p>

                    <fieldset class="jf-import-duplicate">
                        <legend>{{ __('app.settings.import.duplicate_label') }}</legend>
                        <label class="jf-import-radio">
                            <input type="radio" wire:model="duplicateAction" value="skip" />
                            <span>{{ __('app.settings.import.duplicate_skip') }}</span>
                        </label>
                        <label class="jf-import-radio">
                            <input type="radio" wire:model="duplicateAction" value="update" />
                            <span>{{ __('app.settings.import.duplicate_update') }}</span>
                        </label>
                    </fieldset>
                </div>

                <div class="jf-import-actions jf-import-actions--between">
                    <x-filament::button color="gray" wire:click="backToStep2">
                        {{ __('app.settings.import.back') }}
                    </x-filament::button>
                    <x-filament::button
                        color="primary"
                        class="jf-import-btn-primary"
                        x-on:click="runImport()"
                        x-bind:disabled="busy"
                    >
                        <span class="jf-import-btn-content" x-show="! busy">
                            {{ __('app.settings.import.import_now') }}
                        </span>
                        <span class="jf-import-btn-content jf-import-btn-loading" x-show="busy" x-cloak>
                            <span class="jf-import-spinner jf-import-spinner--sm" aria-hidden="true"></span>
                            {{ __('app.settings.import.importing_now') }}
                        </span>
                    </x-filament::button>
                </div>
            @endif

            @if ($importRunStarted)
                @if ($this->showImportProgress)
                    <div
                        class="jf-import-progress-panel"
                        role="status"
                        aria-live="polite"
                        wire:loading.class="jf-import-progress-panel--loading"
                        wire:target="startImport, importChunk"
                    >
                        <div class="jf-import-progress-status">
                            <span class="jf-import-spinner" aria-hidden="true"></span>
                            <div>
                                <p class="jf-import-progress__title">
                                    @if ($importTotal > 0)
                                        {{ __('app.settings.import.progress', ['percent' => $this->importProgressPercent]) }}
                                    @else
                                        {{ __('app.settings.import.preparing') }}
                                    @endif
                                </p>
                                @if ($importTotal > 0)
                                    <p class="jf-import-progress__label">
                                        {{ __('app.settings.import.progress_rows', [
                                            'current' => min($importProgress, $importTotal),
                                            'total' => $importTotal,
                                        ]) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div
                            class="jf-import-progress @if ($importTotal === 0) jf-import-progress--indeterminate @endif"
                            role="progressbar"
                            aria-valuemin="0"
                            aria-valuemax="{{ max($importTotal, 1) }}"
                            aria-valuenow="{{ $importProgress }}"
                        >
                            @if ($importTotal === 0)
                                <div class="jf-import-progress__bar jf-import-progress__bar--indeterminate"></div>
                            @else
                                <div
                                    class="jf-import-progress__bar"
                                    style="width: {{ max($this->importProgressPercent, $importProgress > 0 ? 2 : 0) }}%"
                                ></div>
                            @endif
                        </div>
                        @if ($importTotal > 0)
                            <p class="jf-import-progress__percent">{{ $this->importProgressPercent }}%</p>
                        @endif
                    </div>
                @endif

                @if ($this->showImportResults)
                    <div class="jf-import-results">
                        <div class="jf-import-result-card jf-import-result-card--success">
                            <span class="jf-import-result-card__value">{{ $importedCount + $updatedCount }}</span>
                            <span class="jf-import-result-card__label">{{ __('app.settings.import.result_imported') }}</span>
                        </div>
                        <div class="jf-import-result-card jf-import-result-card--warning">
                            <span class="jf-import-result-card__value">{{ $skippedCount }}</span>
                            <span class="jf-import-result-card__label">{{ __('app.settings.import.result_skipped') }}</span>
                        </div>
                        @if ($subscriptionsCreatedCount > 0)
                            <div class="jf-import-result-card jf-import-result-card--success">
                                <span class="jf-import-result-card__value">{{ $subscriptionsCreatedCount }}</span>
                                <span class="jf-import-result-card__label">{{ __('app.settings.import.result_subscriptions') }}</span>
                            </div>
                        @endif
                        <div class="jf-import-result-card jf-import-result-card--danger">
                            <span class="jf-import-result-card__value">{{ $failedCount }}</span>
                            <span class="jf-import-result-card__label">{{ __('app.settings.import.result_errors') }}</span>
                        </div>
                    </div>

                    @if ($errorReportToken)
                        <div class="jf-import-actions jf-import-actions--start">
                            <a
                                href="{{ route('members.import.errors', ['token' => $errorReportToken]) }}"
                                class="fi-btn fi-btn-size-md fi-color-gray fi-btn-outlined"
                            >
                                <x-filament::icon icon="heroicon-m-arrow-down-tray" class="fi-btn-icon" />
                                {{ __('app.settings.import.download_errors') }}
                            </a>
                        </div>
                    @endif

                    <div class="jf-import-actions jf-import-actions--end">
                        <x-filament::button color="gray" wire:click="backToStep1">
                            {{ __('app.settings.import.import_another') }}
                        </x-filament::button>
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>
