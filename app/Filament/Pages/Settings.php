<?php

namespace App\Filament\Pages;

use App\Contracts\SettingsRepository;
use App\Helpers\Helpers;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use ZipArchive;

/**
 * @property-read Schema $form
 */
class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var string|null Page title */
    protected static ?string $title = null;

    /** @var string View file for the settings page */
    protected string $view = 'filament.pages.settings';

    /** @var array<string, mixed>|null Stores the settings data */
    public ?array $data = [];

    /** @var string|null Stores the uploaded settings file */
    public ?string $settings_file = null;

    /**
     * Mount the page and load settings from the storage.
     */
    public function mount(): void
    {
        $settings = $this->prepareSettingsForForm(Helpers::getSettings());

        $this->form->fill($settings);
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function prepareSettingsForForm(array $settings): array
    {
        $general = is_array($settings['general'] ?? null) ? $settings['general'] : [];

        $logo = $general['gym_logo'] ?? null;
        if (is_array($logo)) {
            $general['gym_logo'] = filled($logo) ? array_values($logo) : null;
        } elseif (! filled($logo)) {
            $general['gym_logo'] = null;
        }

        $settings['general'] = $general;

        return $settings;
    }

    public function getTitle(): string
    {
        return __('app.settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('app.settings.title');
    }

    /**
     * Defines the form schema with multiple tabs.
     *
     * @return array<int, Component>
     */
    protected function getFormSchema(): array
    {
        return [
            Tabs::make(__('app.settings.title'))
                ->tabs([
                    $this->generalTab(),
                    $this->invoiceTab(),
                    $this->memberTab(),
                    $this->chargesTab(),
                    $this->expensesTab(),
                    $this->subscriptionsTab(),
                    $this->importTab(),
                    $this->backupTab(),
                ]),
        ];
    }

    /**
     * General Tab Schema.
     */
    private function generalTab(): Tab
    {
        return Tab::make(__('app.settings.tabs.gym_info'))
            ->icon('heroicon-m-briefcase')
            ->schema([
                Section::make(__('app.settings.sections.general_information'))
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('general.gym_name')
                                    ->label(__('app.settings.fields.gym_name')),
                                Select::make('general.currency')
                                    ->label(__('app.settings.fields.currency'))
                                    ->options(Helpers::getCurrencies())
                                    ->searchable(),
                                FileUpload::make('general.gym_logo')
                                    ->label(__('app.settings.fields.gym_logo'))
                                    ->disk('public')
                                    ->directory('images')
                                    ->preserveFilenames()
                                    ->imageEditor()
                                    ->deletable()
                                    ->visibility('public')
                                    ->image()
                                    ->afterStateUpdated(fn ($state, callable $set) => $this->handleFileUpload($state, 'gym_logo', $set))
                                    ->columnSpanFull(),
                                DatePicker::make('general.financial_year_start')
                                    ->native(false)
                                    ->label(__('app.settings.fields.financial_year_start'))
                                    ->suffixIcon('heroicon-o-calendar-days')
                                    ->displayFormat('d/m/Y'),
                                DatePicker::make('general.financial_year_end')
                                    ->native(false)
                                    ->label(__('app.settings.fields.financial_year_end'))
                                    ->suffixIcon('heroicon-o-calendar-days')
                                    ->displayFormat('d/m/Y'),
                            ]),
                    ])
                    ->columnSpan(3),

                Section::make(__('app.settings.sections.address'))
                    ->aside()
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Textarea::make('general.address')
                                    ->label(__('app.settings.fields.address')),
                            ]),
                        Grid::make(4)
                            ->schema([
                                Select::make('general.country')
                                    ->label(__('app.settings.fields.country'))
                                    ->options(Helpers::getCountries())
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, callable $set) => [
                                        $set('general.state', null),
                                        $set('general.city', null),
                                    ]),
                                Select::make('general.state')
                                    ->label(__('app.settings.fields.state'))
                                    ->options(fn ($get) => Helpers::getStates($get('general.country')))
                                    ->searchable()
                                    ->live(),
                                Select::make('general.city')
                                    ->label(__('app.settings.fields.city'))
                                    ->options(fn ($get) => Helpers::getCities($get('general.state')))
                                    ->searchable()
                                    ->live(),
                                TextInput::make('general.zip')
                                    ->label(__('app.settings.fields.zip'))
                                    ->numeric()
                                    ->maxLength(10),
                            ]),
                    ])
                    ->columnSpan(3),
                Section::make(__('app.settings.sections.contact_information'))
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('general.gym_email')
                                    ->label(__('app.settings.fields.email_address'))
                                    ->email()
                                    ->prefixIcon('heroicon-o-envelope'),
                                TextInput::make('general.gym_contact')
                                    ->numeric()
                                    ->prefixIcon('heroicon-o-phone')
                                    ->label(__('app.settings.fields.contact_no')),
                            ]),
                    ])
                    ->columnSpan(3),
            ]);
    }

    /**
     * Invoice Tab Schema.
     */
    private function invoiceTab(): Tab
    {
        return
            Tab::make(__('app.settings.tabs.invoice'))->icon('heroicon-m-document-text')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('invoice.prefix')
                                ->placeholder(__('app.settings.placeholders.prefix'))
                                ->label(__('app.settings.fields.prefix')),
                            TextInput::make('invoice.last_number')
                                ->numeric()
                                ->label(__('app.settings.fields.last_number'))
                                ->maxLength(10),
                            Select::make('invoice.name_type')
                                ->native(false)
                                ->label(__('app.settings.fields.name_type'))
                                ->options([
                                    'gym_name' => __('app.settings.options.name_type.gym_name'),
                                    'gym_logo' => __('app.settings.options.name_type.gym_logo'),
                                ]),
                        ]),
                    Fieldset::make(__('app.settings.sections.email'))
                        ->columns(['default' => 1, 'md' => 5])
                        ->schema([
                            Group::make()
                                ->schema([
                                    TextInput::make('notifications.email.invoice_subject_template')
                                        ->label(__('app.settings.fields.email_invoice_subject'))
                                        ->placeholder(__('app.settings.placeholders.invoice_email_subject'))
                                        ->helperText(__('app.settings.hints.tokens_invoice')),
                                    TextInput::make('notifications.email.receipt_subject_template')
                                        ->label(__('app.settings.fields.email_receipt_subject'))
                                        ->placeholder(__('app.settings.placeholders.receipt_email_subject'))
                                        ->helperText(__('app.settings.hints.tokens_receipt')),
                                ])->columnSpan(['default' => 1, 'md' => 3]),
                            Group::make()
                                ->schema([
                                    Toggle::make('notifications.email.enabled')
                                        ->label(__('app.settings.fields.email_enabled'))
                                        ->default(false)
                                        ->inlineLabel(),
                                    Toggle::make('notifications.email.auto_send_invoice_issued')
                                        ->label(__('app.settings.fields.auto_send_invoice_issued'))
                                        ->default(false)
                                        ->inlineLabel(),
                                    Toggle::make('notifications.email.auto_send_payment_receipt')
                                        ->label(__('app.settings.fields.auto_send_payment_receipt'))
                                        ->default(false)
                                        ->inlineLabel(),
                                ])
                                ->columns(1)
                                ->columnSpan(['default' => 1, 'md' => 2]),
                        ]),
                ]);
    }

    /**
     * Member Tab Schema.
     */
    private function memberTab(): Tab
    {
        return
            Tab::make(__('app.settings.tabs.member'))->icon('heroicon-m-user-group')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('member.prefix')
                                ->placeholder(__('app.settings.placeholders.prefix'))
                                ->label(__('app.settings.fields.prefix')),
                            TextInput::make('member.last_number')
                                ->numeric()
                                ->label(__('app.settings.fields.last_number'))
                                ->maxLength(10),
                        ]),
                ]);
    }

    /**
     * Charges Tab Schema.
     */
    private function chargesTab(): Tab
    {
        return
            Tab::make(__('app.settings.tabs.charges'))->icon('heroicon-m-currency-rupee')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('charges.admission_fee')
                                ->numeric()
                                ->label(__('app.settings.fields.admission_fee')),
                            TextInput::make('charges.taxes')
                                ->numeric()
                                ->label(__('app.settings.fields.taxes'))
                                ->suffix('%'),
                            TagsInput::make('charges.discounts')
                                ->label(__('app.settings.fields.discount_percent_available'))
                                ->hint(__('app.settings.hints.press_enter_to_add'))
                                ->placeholder(__('app.settings.hints.type_discount'))
                                ->separator(','),
                        ]),
                ]);
    }

    /**
     * Expenses Tab Schema.
     */
    private function expensesTab(): Tab
    {
        return
            Tab::make(__('app.settings.tabs.expenses'))->icon('heroicon-m-banknotes')
                ->schema([
                    TagsInput::make('expenses.categories')
                        ->label(__('app.settings.fields.categories'))
                        ->hint(__('app.settings.hints.press_enter_to_add'))
                        ->placeholder(__('app.settings.hints.type_category'))
                        ->separator(','),
                ]);
    }

    /**
     * Member import wizard tab.
     */
    private function importTab(): Tab
    {
        return Tab::make(__('app.settings.tabs.import'))
            ->icon('heroicon-m-arrow-up-tray')
            ->schema([
                View::make('filament.settings.member-import-tab'),
            ]);
    }

    /**
     * Subscriptions Tab Schema.
     */
    private function subscriptionsTab(): Tab
    {
        return
            Tab::make(__('app.settings.tabs.subscriptions'))->icon('heroicon-m-ticket')
                ->schema([
                    TextInput::make('subscriptions.expiring_days')
                        ->label(__('app.settings.fields.expiring_days'))
                        ->numeric()
                        ->minValue(1)
                        ->default(7),
                    Section::make(__('app.settings.sections.checkin'))
                        ->schema([
                            TextInput::make('checkin.present_now_grace_minutes')
                                ->label(__('app.settings.fields.present_now_grace_minutes'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(120)
                                ->default(15)
                                ->helperText(__('app.settings.hints.present_now_grace_minutes')),
                        ]),
                ]);
    }

    /**
     * Backup Tab Schema.
     */
    private function backupTab(): Tab
    {
        return Tab::make(__('app.settings.tabs.backup'))
            ->icon('heroicon-m-archive-box')
            ->schema([
                Section::make(__('app.settings.sections.backup_config'))
                    ->aside()
                    ->description(__('app.settings.sections.backup_config_desc'))
                    ->schema([
                        Toggle::make('backup.enabled')
                            ->label(__('app.settings.fields.backup_enabled'))
                            ->inlineLabel()
                            ->columnSpanFull(),
                        TextInput::make('backup.path')
                            ->label(__('app.settings.fields.backup_path'))
                            ->placeholder(__('app.settings.placeholders.backup_path'))
                            ->helperText(__('app.settings.hints.backup_path'))
                            ->columnSpanFull(),
                        Select::make('backup.trigger')
                            ->label(__('app.settings.fields.backup_trigger'))
                            ->native(false)
                            ->options([
                                'after_member' => __('app.settings.options.backup_trigger.after_member'),
                                'end_of_day' => __('app.settings.options.backup_trigger.end_of_day'),
                                'both' => __('app.settings.options.backup_trigger.both'),
                            ])
                            ->default('end_of_day'),
                        TextInput::make('backup.end_of_day_time')
                            ->label(__('app.settings.fields.backup_end_of_day_time'))
                            ->placeholder('22:00')
                            ->helperText(__('app.settings.hints.backup_time_format'))
                            ->visible(fn ($get) => in_array($get('backup.trigger'), ['end_of_day', 'both'])),
                        TextInput::make('backup.keep_backups')
                            ->label(__('app.settings.fields.backup_keep'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(365)
                            ->default(7),
                    ])
                    ->columnSpan(3),

                Section::make(__('app.settings.sections.backup_manual'))
                    ->schema([
                        Actions::make([
                            Action::make('runBackupNow')
                                ->label(__('app.settings.actions.backup_now'))
                                ->icon('heroicon-o-archive-box-arrow-down')
                                ->color('success')
                                ->action(fn () => $this->runBackupNow()),
                        ]),
                        View::make('filament.settings.backup-status'),
                    ])
                    ->columnSpan(3),

                Section::make(__('app.settings.sections.backup_restore'))
                    ->description(__('app.settings.sections.backup_restore_desc'))
                    ->aside()
                    ->schema([
                        Actions::make([
                            Action::make('restoreFromBackup')
                                ->label(__('app.settings.actions.restore_now'))
                                ->icon('heroicon-o-arrow-path')
                                ->color('danger')
                                ->modalHeading(__('app.settings.backup.restore_heading'))
                                ->modalDescription(__('app.settings.backup.restore_warning'))
                                ->modalSubmitActionLabel(__('app.settings.actions.restore_confirm'))
                                ->modalIcon('heroicon-o-exclamation-triangle')
                                ->form([
                                    FileUpload::make('backup_zip')
                                        ->label(__('app.settings.fields.restore_zip'))
                                        ->disk('local')
                                        ->directory('restore-tmp')
                                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'application/octet-stream'])
                                        ->required(),
                                    Toggle::make('include_settings')
                                        ->label(__('app.settings.fields.restore_include_settings'))
                                        ->helperText(__('app.settings.hints.restore_include_settings'))
                                        ->default(false)
                                        ->inlineLabel(),
                                ])
                                ->action(fn (array $data) => $this->restoreFromBackup($data)),
                        ]),
                    ])
                    ->columnSpan(3),
            ]);
    }

    /**
     * Run an immediate backup and notify the user.
     */
    public function runBackupNow(): void
    {
        $settings = $this->form->getState()['backup'] ?? [];

        if (empty($settings['enabled'])) {
            Notification::make()
                ->title(__('app.notifications.backup_disabled'))
                ->warning()
                ->send();

            return;
        }

        if (empty(trim((string) ($settings['path'] ?? '')))) {
            Notification::make()
                ->title(__('app.notifications.backup_path_missing'))
                ->danger()
                ->send();

            return;
        }

        $exitCode = Artisan::call('app:backup', ['--trigger' => 'manual']);

        if ($exitCode === 0) {
            Notification::make()
                ->title(__('app.notifications.backup_success'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('app.notifications.backup_failed'))
                ->danger()
                ->send();
        }
    }

    /**
     * Restore database (and optionally settings) from an uploaded backup ZIP.
     *
     * @param  array<string, mixed>  $data
     */
    public function restoreFromBackup(array $data): void
    {
        $relPath = is_array($data['backup_zip']) ? ($data['backup_zip'][0] ?? '') : (string) ($data['backup_zip'] ?? '');
        $zipPath = storage_path('app/'.$relPath);

        if (! file_exists($zipPath)) {
            Notification::make()->title(__('app.notifications.restore_zip_not_found'))->danger()->send();

            return;
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            Notification::make()->title(__('app.notifications.restore_zip_invalid'))->danger()->send();
            @unlink($zipPath);

            return;
        }

        if ($zip->locateName('database.sqlite') === false) {
            Notification::make()->title(__('app.notifications.restore_no_database'))->danger()->send();
            $zip->close();
            @unlink($zipPath);

            return;
        }

        $tempDir = storage_path('app/restore-tmp-extract-'.time());
        mkdir($tempDir, 0755, true);
        $zip->extractTo($tempDir);
        $zip->close();

        $newDbPath = $tempDir.DIRECTORY_SEPARATOR.'database.sqlite';

        if (! $this->isValidSQLite($newDbPath)) {
            $this->cleanupTempDir($tempDir);
            @unlink($zipPath);
            Notification::make()->title(__('app.notifications.restore_invalid_database'))->danger()->send();

            return;
        }

        // Safety backup of current state before overwriting
        Artisan::call('app:backup', ['--trigger' => 'pre-restore', '--force' => true]);

        $dbPath = database_path('database.sqlite');

        DB::disconnect();

        if (! copy($newDbPath, $dbPath)) {
            DB::reconnect();
            $this->cleanupTempDir($tempDir);
            @unlink($zipPath);
            Notification::make()->title(__('app.notifications.restore_failed'))->danger()->send();

            return;
        }

        if (! empty($data['include_settings'])) {
            $settingsSource = $tempDir.DIRECTORY_SEPARATOR.'settingsData.json';
            if (file_exists($settingsSource)) {
                copy($settingsSource, storage_path('data/settingsData.json'));
                $decoded = json_decode((string) file_get_contents($settingsSource), true);
                if (is_array($decoded)) {
                    app(SettingsRepository::class)->put($decoded);
                }
            }
        }

        $this->cleanupTempDir($tempDir);
        @unlink($zipPath);

        DB::reconnect();

        Notification::make()
            ->title(__('app.notifications.restore_success'))
            ->success()
            ->send();
    }

    private function isValidSQLite(string $path): bool
    {
        if (! file_exists($path) || filesize($path) < 16) {
            return false;
        }

        $handle = fopen($path, 'rb');
        $magic = fread($handle, 16);
        fclose($handle);

        return str_starts_with((string) $magic, 'SQLite format 3');
    }

    private function cleanupTempDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($dir);
    }

    /**
     * Configures a form instance by setting its schema and state path.
     *
     * @param  Schema  $schema  The form instance to configure.
     * @return Schema The configured form instance.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getFormSchema())
            ->statePath('data');
    }

    /**
     * Persist the current settings.
     */
    public function save(): void
    {
        $settings = $this->form->getState();
        $general = is_array($settings['general'] ?? null) ? $settings['general'] : [];

        if (! empty($general['financial_year_start']) && is_string($general['financial_year_start'])) {
            $general['financial_year_start'] =
                Carbon::parse($general['financial_year_start'])
                    ->toDateString();
        }
        if (! empty($general['financial_year_end']) && is_string($general['financial_year_end'])) {
            $general['financial_year_end'] =
                Carbon::parse($general['financial_year_end'])
                    ->toDateString();
        }

        foreach (['gym_logo'] as $logoKey) {
            $value = $general[$logoKey] ?? null;
            if (is_array($value)) {
                $general[$logoKey] = $value[0] ?? null;
            }
        }

        $settings['general'] = $general;

        try {
            app(SettingsRepository::class)->put($settings);
            $this->form->fill($this->prepareSettingsForForm($settings));
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('app.notifications.failed'))
                ->body(__('app.notifications.failed_settings_save'))
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title(__('app.notifications.success'))
            ->body(__('app.notifications.success_settings_save'))
            ->success()
            ->send();
    }

    /**
     * @return array<int, Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('app.settings.actions.save_settings'))
                ->submit('save'),
        ];
    }

    /**
     * Handles the file upload process and updates the settings data.
     *
     * @param  TemporaryUploadedFile|string|null  $state  The uploaded file state.
     * @param  string  $key  The key to store the uploaded file path in the settings.
     * @param  callable  $set  The callback to update the form state.
     */
    private function handleFileUpload(mixed $state, string $key, callable $set): void
    {
        if (! $state instanceof TemporaryUploadedFile) {
            return;
        }

        $path = $state->storeAs('images', $state->getClientOriginalName(), 'public');
        $repository = app(SettingsRepository::class);
        $settings = $repository->get();
        $general = is_array($settings['general'] ?? null) ? $settings['general'] : [];

        $general[$key] = $path;
        $settings['general'] = $general;

        $repository->put($settings);

        // Update the form state
        $set("general.$key", [$path]);
    }
}
