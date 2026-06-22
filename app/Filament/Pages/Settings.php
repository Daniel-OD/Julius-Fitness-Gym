<?php

namespace App\Filament\Pages;

use App\Contracts\SettingsRepository;
use App\Helpers\Helpers;
use App\Mail\TestMailConfigurationMail;
use App\Services\WhatsAppService;
use App\Support\MailConfigurator;
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
use Illuminate\Support\Facades\Mail;
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

        $mail = array_merge(
            MailConfigurator::defaultMailSettings(),
            is_array($settings['mail'] ?? null) ? $settings['mail'] : [],
        );
        $mail['resend_api_key'] = '';
        $mail['smtp_password'] = '';
        $settings['mail'] = $mail;

        return $settings;
    }

    #[\Override]
    public function getTitle(): string
    {
        return __('app.settings.title');
    }

    #[\Override]
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
                ->persistTabInQueryString('tab')
                ->tabs([
                    $this->generalTab(),
                    $this->invoiceTab(),
                    $this->mailTab(),
                    $this->whatsAppTab(),
                    $this->memberTab(),
                    $this->chargesTab(),
                    $this->expensesTab(),
                    $this->subscriptionsTab(),
                    $this->importTab(),
                    $this->backupTab(),
                ]),
        ];
    }

    private function guidePanel(string $tabId): View
    {
        return View::make('filament.components.admin-guide-panel')
            ->viewData(['guideKey' => "admin.settings.tabs.{$tabId}"])
            ->columnSpanFull();
    }

    /**
     * Returns cascading country → state → city Selects when the world package is seeded,
     * or plain TextInputs when it is not (avoids empty, unusable dropdowns).
     *
     * @return array<int, Component>
     */
    private function locationFields(): array
    {
        $worldAvailable = count(Helpers::getCountries()) > 1;

        if ($worldAvailable) {
            return [
                Select::make('general.country')
                    ->label(__('app.settings.fields.country'))
                    ->options(fn (): array => Helpers::getCountries())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set): array => [
                        $set('general.state', null),
                        $set('general.city', null),
                    ]),
                Select::make('general.state')
                    ->label(__('app.settings.fields.state'))
                    ->options(fn ($get): array => Helpers::getStates($get('general.country')))
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('general.city')
                    ->label(__('app.settings.fields.city'))
                    ->options(fn ($get): array => Helpers::getCities($get('general.state')))
                    ->searchable()
                    ->preload()
                    ->live(),
            ];
        }

        return [
            TextInput::make('general.country')
                ->label(__('app.settings.fields.country')),
            TextInput::make('general.state')
                ->label(__('app.settings.fields.state')),
            TextInput::make('general.city')
                ->label(__('app.settings.fields.city')),
        ];
    }

    /**
     * General Tab Schema.
     */
    private function generalTab(): Tab
    {
        return Tab::make(__('app.settings.tabs.gym_info'))
            ->id('gym_info')
            ->icon('heroicon-m-briefcase')
            ->schema([
                $this->guidePanel('gym_info'),
                Section::make(__('app.settings.sections.general_information'))
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('general.gym_name')
                                    ->label(__('app.settings.fields.gym_name')),
                                Select::make('general.currency')
                                    ->label(__('app.settings.fields.currency'))
                                    ->options(fn (): array => Helpers::getCurrencies())
                                    ->searchable()
                                    ->preload(),
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
                                ...$this->locationFields(),
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
                ->id('invoice')
                ->schema([
                    $this->guidePanel('invoice'),
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
     * Mail delivery tab (transport: env, Resend, SMTP, log, sendmail).
     */
    private function mailTab(): Tab
    {
        return Tab::make(__('app.settings.tabs.mail'))
            ->id('mail')
            ->icon('heroicon-m-envelope')
            ->schema([
                $this->guidePanel('mail'),
                Section::make(__('app.settings.sections.mail_delivery'))
                    ->description(__('app.settings.sections.mail_delivery_desc'))
                    ->aside()
                    ->schema([
                        Select::make('mail.driver')
                            ->label(__('app.settings.fields.mail_driver'))
                            ->native(false)
                            ->options([
                                MailConfigurator::DRIVER_ENV => __('app.settings.options.mail_driver.env'),
                                MailConfigurator::DRIVER_RESEND => __('app.settings.options.mail_driver.resend'),
                                MailConfigurator::DRIVER_SMTP => __('app.settings.options.mail_driver.smtp'),
                                MailConfigurator::DRIVER_LOG => __('app.settings.options.mail_driver.log'),
                                MailConfigurator::DRIVER_SENDMAIL => __('app.settings.options.mail_driver.sendmail'),
                            ])
                            ->default(MailConfigurator::DRIVER_ENV)
                            ->live()
                            ->helperText(__('app.settings.hints.mail_driver')),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('mail.from_address')
                                    ->label(__('app.settings.fields.mail_from_address'))
                                    ->email()
                                    ->placeholder(__('app.settings.placeholders.mail_from_address')),
                                TextInput::make('mail.from_name')
                                    ->label(__('app.settings.fields.mail_from_name'))
                                    ->placeholder(__('app.settings.placeholders.mail_from_name')),
                            ]),
                    ])
                    ->columnSpan(3),
                Section::make(__('app.settings.sections.mail_resend'))
                    ->aside()
                    ->visible(fn ($get): bool => $get('mail.driver') === MailConfigurator::DRIVER_RESEND)
                    ->schema([
                        TextInput::make('mail.resend_api_key')
                            ->label(__('app.settings.fields.resend_api_key'))
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->placeholder(__('app.settings.placeholders.resend_api_key'))
                            ->helperText(__('app.settings.hints.resend_api_key')),
                    ])
                    ->columnSpan(3),
                Section::make(__('app.settings.sections.mail_smtp'))
                    ->aside()
                    ->visible(fn ($get): bool => $get('mail.driver') === MailConfigurator::DRIVER_SMTP)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('mail.smtp_host')
                                    ->label(__('app.settings.fields.smtp_host'))
                                    ->placeholder('smtp.example.com'),
                                TextInput::make('mail.smtp_port')
                                    ->label(__('app.settings.fields.smtp_port'))
                                    ->numeric()
                                    ->default(587),
                                TextInput::make('mail.smtp_username')
                                    ->label(__('app.settings.fields.smtp_username')),
                                TextInput::make('mail.smtp_password')
                                    ->label(__('app.settings.fields.smtp_password'))
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn (?string $state): bool => filled($state)),
                                Select::make('mail.smtp_encryption')
                                    ->label(__('app.settings.fields.smtp_encryption'))
                                    ->native(false)
                                    ->options([
                                        'tls' => 'TLS',
                                        'ssl' => 'SSL',
                                        'none' => __('app.settings.options.smtp_encryption.none'),
                                    ])
                                    ->default('tls'),
                            ]),
                    ])
                    ->columnSpan(3),
                Section::make(__('app.settings.sections.mail_test'))
                    ->schema([
                        Actions::make([
                            Action::make('sendTestEmail')
                                ->label(__('app.settings.actions.send_test_email'))
                                ->icon('heroicon-o-paper-airplane')
                                ->color('gray')
                                ->action(fn () => $this->sendTestEmail()),
                        ]),
                    ])
                    ->columnSpan(3),
            ]);
    }

    /**
     * WhatsApp Tab Schema.
     */
    private function whatsAppTab(): Tab
    {
        return Tab::make(__('app.settings.tabs.whatsapp'))
            ->id('whatsapp')
            ->icon('heroicon-m-chat-bubble-left-right')
            ->schema([
                $this->guidePanel('whatsapp'),
                Section::make(__('app.settings.whatsapp.section_connection'))
                    ->aside()
                    ->schema([
                        Toggle::make('notifications.whatsapp.enabled')
                            ->label(__('app.settings.whatsapp.fields.enabled'))
                            ->default(false)
                            ->inlineLabel()
                            ->columnSpanFull(),
                        Select::make('notifications.whatsapp.provider')
                            ->label(__('app.settings.whatsapp.fields.provider'))
                            ->native(false)
                            ->options([
                                WhatsAppService::PROVIDER_META => __('app.settings.whatsapp.options.provider.meta'),
                                WhatsAppService::PROVIDER_TWILIO => __('app.settings.whatsapp.options.provider.twilio'),
                                WhatsAppService::PROVIDER_VONAGE => __('app.settings.whatsapp.options.provider.vonage'),
                            ])
                            ->default(WhatsAppService::PROVIDER_META)
                            ->live(),
                        TextInput::make('notifications.whatsapp.api_key')
                            ->label(__('app.settings.whatsapp.fields.api_key'))
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(__('app.settings.whatsapp.hints.api_key')),
                        TextInput::make('notifications.whatsapp.api_secret')
                            ->label(__('app.settings.whatsapp.fields.api_secret'))
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(__('app.settings.whatsapp.hints.api_secret'))
                            ->visible(fn ($get): bool => $get('notifications.whatsapp.provider') === WhatsAppService::PROVIDER_VONAGE),
                        TextInput::make('notifications.whatsapp.account_sid')
                            ->label(__('app.settings.whatsapp.fields.account_sid'))
                            ->helperText(__('app.settings.whatsapp.hints.account_sid'))
                            ->visible(fn ($get): bool => $get('notifications.whatsapp.provider') === WhatsAppService::PROVIDER_TWILIO),
                        TextInput::make('notifications.whatsapp.phone_number_id')
                            ->label(__('app.settings.whatsapp.fields.phone_number_id'))
                            ->helperText(__('app.settings.whatsapp.hints.phone_number_id')),
                    ])
                    ->columnSpan(3),
                Section::make(__('app.settings.whatsapp.section_templates'))
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('notifications.whatsapp.templates.subscription_expiry')
                                    ->label(__('app.settings.whatsapp.fields.template_subscription_expiry'))
                                    ->helperText(__('app.settings.whatsapp.hints.template')),
                                TextInput::make('notifications.whatsapp.templates.payment_confirmation')
                                    ->label(__('app.settings.whatsapp.fields.template_payment_confirmation'))
                                    ->helperText(__('app.settings.whatsapp.hints.template')),
                                TextInput::make('notifications.whatsapp.templates.welcome')
                                    ->label(__('app.settings.whatsapp.fields.template_welcome'))
                                    ->helperText(__('app.settings.whatsapp.hints.template')),
                                TextInput::make('notifications.whatsapp.templates.birthday')
                                    ->label(__('app.settings.whatsapp.fields.template_birthday'))
                                    ->helperText(__('app.settings.whatsapp.hints.template')),
                            ]),
                    ])
                    ->columnSpan(3),
                Section::make(__('app.settings.whatsapp.section_test'))
                    ->schema([
                        Actions::make([
                            Action::make('sendTestWhatsApp')
                                ->label(__('app.settings.whatsapp.actions.test_connection'))
                                ->icon('heroicon-o-paper-airplane')
                                ->color('gray')
                                ->form([
                                    TextInput::make('test_phone')
                                        ->label(__('app.settings.whatsapp.fields.test_phone'))
                                        ->helperText(__('app.settings.whatsapp.hints.test_phone'))
                                        ->required(),
                                ])
                                ->action(fn (array $data) => $this->sendTestWhatsApp($data['test_phone'])),
                        ]),
                    ])
                    ->columnSpan(3),
            ]);
    }

    /**
     * Send a test WhatsApp message to the given phone number.
     */
    public function sendTestWhatsApp(string $phone): void
    {
        if (! filled($phone)) {
            Notification::make()
                ->title(__('app.settings.whatsapp.notifications.test_no_phone'))
                ->warning()
                ->send();

            return;
        }

        $whatsApp = app(WhatsAppService::class);

        if (! $whatsApp->isEnabled()) {
            Notification::make()
                ->title(__('app.settings.whatsapp.notifications.test_disabled'))
                ->warning()
                ->send();

            return;
        }

        $settings = $this->form->getState();
        $gymName = (string) (data_get($settings, 'general.gym_name') ?: config('app.name'));

        $sent = $whatsApp->sendMessage(
            phone: $phone,
            template: (string) (data_get($settings, 'notifications.whatsapp.templates.welcome') ?: 'hello_world'),
            variables: [$gymName],
        );

        if ($sent) {
            Notification::make()
                ->title(__('app.settings.whatsapp.notifications.test_success', ['phone' => $phone]))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('app.settings.whatsapp.notifications.test_failed'))
                ->danger()
                ->send();
        }
    }

    /**
     * Member Tab Schema.
     */
    private function memberTab(): Tab
    {
        return
            Tab::make(__('app.settings.tabs.member'))->icon('heroicon-m-user-group')
                ->id('member')
                ->schema([
                    $this->guidePanel('member'),
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
                ->id('charges')
                ->schema([
                    $this->guidePanel('charges'),
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
                ->id('expenses')
                ->schema([
                    $this->guidePanel('expenses'),
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
            ->id('import')
            ->icon('heroicon-m-arrow-up-tray')
            ->schema([
                $this->guidePanel('import'),
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
                ->id('subscriptions')
                ->schema([
                    $this->guidePanel('subscriptions'),
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
            ->id('backup')
            ->icon('heroicon-m-archive-box')
            ->schema([
                $this->guidePanel('backup'),
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
                            ->visible(fn ($get): bool => in_array($get('backup.trigger'), ['end_of_day', 'both'])),
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

        $existing = app(SettingsRepository::class)->get();
        $existingMail = is_array($existing['mail'] ?? null) ? $existing['mail'] : [];
        $mail = is_array($settings['mail'] ?? null) ? $settings['mail'] : [];

        if (! filled($mail['resend_api_key'] ?? null)) {
            $mail['resend_api_key'] = (string) ($existingMail['resend_api_key'] ?? '');
        }

        if (! filled($mail['smtp_password'] ?? null)) {
            $mail['smtp_password'] = (string) ($existingMail['smtp_password'] ?? '');
        }

        $settings['mail'] = $mail;

        $existingWhatsApp = is_array($existing['notifications']['whatsapp'] ?? null)
            ? $existing['notifications']['whatsapp']
            : [];
        $whatsApp = is_array($settings['notifications']['whatsapp'] ?? null)
            ? $settings['notifications']['whatsapp']
            : [];

        if (! filled($whatsApp['api_key'] ?? null)) {
            $whatsApp['api_key'] = (string) ($existingWhatsApp['api_key'] ?? '');
        }

        if (! filled($whatsApp['api_secret'] ?? null)) {
            $whatsApp['api_secret'] = (string) ($existingWhatsApp['api_secret'] ?? '');
        }

        if (! isset($settings['notifications'])) {
            $settings['notifications'] = [];
        }

        $settings['notifications']['whatsapp'] = $whatsApp;

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
     * Send a test email using the current form mail settings (saved or unsaved).
     */
    public function sendTestEmail(): void
    {
        $user = auth()->user();

        if ($user === null || ! filled($user->email)) {
            Notification::make()
                ->title(__('app.notifications.failed'))
                ->body(__('app.settings.mail.test_no_recipient'))
                ->danger()
                ->send();

            return;
        }

        $formState = $this->form->getState();

        MailConfigurator::apply($formState);

        // Force-purge any cached mailer so the new config (API key, host, etc.) is picked up.
        Mail::forgetMailers();

        $gymName = (string) (data_get($formState, 'general.gym_name') ?: config('app.name'));

        try {
            Mail::to($user->email)->send(new TestMailConfigurationMail($gymName));

            Notification::make()
                ->title(__('app.settings.mail.test_sent_title'))
                ->body(__('app.settings.mail.test_sent_body', ['email' => $user->email]))
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title(__('app.settings.mail.test_failed_title'))
                ->body($this->formatMailException($exception))
                ->danger()
                ->send();
        }
    }

    private function formatMailException(\Throwable $exception): string
    {
        $message = $exception->getMessage();

        // Surface the Resend API error body when available (e.g. domain not verified, invalid key).
        if ($exception->getPrevious() !== null) {
            $inner = $exception->getPrevious()->getMessage();
            if (filled($inner) && strlen($inner) < 300) {
                $message = $inner;
            }
        }

        // Trim to a readable length for the notification body.
        return mb_strlen($message) > 250 ? mb_substr($message, 0, 247).'…' : $message;
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
