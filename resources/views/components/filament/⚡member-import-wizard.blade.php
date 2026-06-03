<?php

use App\Enums\MemberImportField;
use App\Services\Members\MemberImportColumnMapper;
use App\Services\Members\MemberImportService;
use App\Services\Members\MemberImportSpreadsheetReader;
use App\Support\Members\MemberImportDataset;
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

    public int $importProgress = 0;

    public int $importTotal = 0;

    public int $importedCount = 0;

    public int $skippedCount = 0;

    public int $updatedCount = 0;

    public int $failedCount = 0;

    public ?string $errorReportToken = null;

    public ?int $summaryImportable = null;

    public ?int $summaryErrors = null;

    public function updatedImportFile(): void
    {
        $this->resetImportState(keepFile: false);
        $this->validate([
            'importFile' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        $extension = strtolower($this->importFile->getClientOriginalExtension());
        $path = $this->importFile->storeAs(
            'member-imports/'.auth()->id(),
            Str::uuid().'.'.$extension,
            'local',
        );

        $this->storedFilePath = $path;
        $this->storedExtension = $extension;

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

        $analysis = $this->importService()->analyze($this->mappedRows());
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

    public function startImport(): void
    {
        $this->resetImportProgress();
        $this->importing = true;
        $this->importTotal = count($this->mappedRows());
        $this->importProgress = 0;
    }

    public function importChunk(int $offset): array
    {
        $result = $this->importService()->importChunk(
            $this->mappedRows(),
            $offset,
            $this->duplicateAction,
        );

        $this->importedCount += $result->imported;
        $this->skippedCount += $result->skippedDuplicates;
        $this->updatedCount += $result->updated;
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

    private function resetImportProgress(): void
    {
        $this->importing = false;
        $this->importProgress = 0;
        $this->importTotal = 0;
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->updatedCount = 0;
        $this->failedCount = 0;
        $this->errorReportToken = null;
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
                <div @class(['jf-import-step__connector', 'jf-import-step__connector--complete' => $step > $number])"></div>
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
                        $wire.upload('importFile', file, () => {}, () => {}, () => {});
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
                    href="{{ route('members.import.template') }}"
                    class="fi-btn fi-btn-size-md fi-color-gray fi-btn-outlined"
                    download
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
        <div class="jf-import-panel">
            @if (! $importing && $importProgress === 0 && $importedCount === 0 && $skippedCount === 0 && $failedCount === 0 && $updatedCount === 0)
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
                        x-data
                        x-on:click="
                            $wire.startImport().then(() => {
                                let offset = 0;
                                const run = () => {
                                    $wire.importChunk(offset).then((result) => {
                                        offset += {{ \App\Services\Members\MemberImportService::CHUNK_SIZE }};
                                        if (! result.done) {
                                            run();
                                        }
                                    });
                                };
                                run();
                            });
                        "
                    >
                        {{ __('app.settings.import.import_now') }}
                    </x-filament::button>
                </div>
            @else
                @if ($importing || ($importProgress > 0 && $importProgress < $importTotal))
                    <div class="jf-import-progress">
                        <div class="jf-import-progress__bar" style="width: {{ $this->importProgressPercent }}%"></div>
                    </div>
                    <p class="jf-import-progress__label">
                        {{ __('app.settings.import.progress', ['percent' => $this->importProgressPercent]) }}
                    </p>
                @endif

                @if (! $importing && $importProgress >= $importTotal && $importTotal > 0)
                    <div class="jf-import-results">
                        <div class="jf-import-result-card jf-import-result-card--success">
                            <span class="jf-import-result-card__value">{{ $importedCount + $updatedCount }}</span>
                            <span class="jf-import-result-card__label">{{ __('app.settings.import.result_imported') }}</span>
                        </div>
                        <div class="jf-import-result-card jf-import-result-card--warning">
                            <span class="jf-import-result-card__value">{{ $skippedCount }}</span>
                            <span class="jf-import-result-card__label">{{ __('app.settings.import.result_skipped') }}</span>
                        </div>
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
