<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Filament\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Helpers\Helpers;
use App\Models\Member;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MemberForm
{
    /**
     * Schema for Step 1 of the member onboarding wizard.
     *
     * Required fields (name, email, contact) are always visible.
     * Optional profile fields are grouped in a collapsed section so
     * the wizard feels lightweight during quick sign-ups.
     *
     * @return array<int, mixed>
     */
    public static function onboardingMemberStep(): array
    {
        return [
            Group::make()
                ->columnSpanFull()
                ->columns(['default' => 1, 'sm' => 2])
                ->extraAttributes(['class' => 'jf-onboarding-essentials'])
                ->schema([
                    TextInput::make('name')
                        ->label(__('app.fields.name'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(['default' => 1, 'sm' => 2]),
                    TextInput::make('email')
                        ->label(__('app.fields.email'))
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->rule(Rule::unique('members', 'email')),
                    TextInput::make('contact')
                        ->label(__('app.fields.contact'))
                        ->tel()
                        ->required()
                        ->maxLength(20),
                ]),
            Section::make(__('app.ui.optional_details'))
                ->description(__('app.ui.optional_details_hint'))
                ->icon('heroicon-o-chevron-down')
                ->collapsible()
                ->collapsed()
                ->columnSpanFull()
                ->columns(['default' => 1, 'md' => 2])
                ->extraAttributes(['class' => 'jf-onboarding-optional'])
                ->schema([
                    Select::make('gender')
                        ->label(__('app.fields.gender'))
                        ->options([
                            'male' => __('app.options.gender.male'),
                            'female' => __('app.options.gender.female'),
                            'other' => __('app.options.gender.other'),
                        ])
                        ->default('male')
                        ->selectablePlaceholder(false),
                    DatePicker::make('dob')
                        ->label(__('app.fields.dob')),
                    Textarea::make('address')
                        ->label(__('app.fields.address'))
                        ->rows(3)
                        ->columnSpanFull(),
                    Select::make('country')
                        ->label(__('app.fields.country'))
                        ->options(Helpers::getCountries())
                        ->reactive()
                        ->afterStateUpdated(fn (Set $set): array => [$set('state', null), $set('city', null)]),
                    Select::make('state')
                        ->label(__('app.fields.state'))
                        ->options(fn (Get $get): array => Helpers::getStates($get('country')))
                        ->reactive(),
                    Select::make('city')
                        ->label(__('app.fields.city'))
                        ->options(fn (Get $get): array => Helpers::getCities($get('state')))
                        ->reactive(),
                    TextInput::make('pincode')
                        ->label(__('app.fields.pincode'))
                        ->numeric(),
                    Select::make('source')
                        ->label(__('app.fields.source'))
                        ->options([
                            'promotions' => __('app.options.source.promotions'),
                            'word_of_mouth' => __('app.options.source.word_of_mouth'),
                            'others' => __('app.options.source.others'),
                        ])
                        ->selectablePlaceholder(false),
                    Select::make('goal')
                        ->label(__('app.fields.goal'))
                        ->options([
                            'fitness' => __('app.options.goal.fitness'),
                            'body_building' => __('app.options.goal.body_building'),
                            'fatloss' => __('app.options.goal.fatloss'),
                            'weightgain' => __('app.options.goal.weightgain'),
                            'others' => __('app.options.goal.others'),
                        ])
                        ->selectablePlaceholder(false),
                ]),
        ];
    }

    /**
     * Configure the member form schema.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        FileUpload::make('photo')
                            ->imageEditor()
                            ->preserveFilenames()
                            ->maxSize(1024 * 1024 * 10)
                            ->disk('public')
                            ->directory('images')
                            ->image()
                            ->placeholder(__('app.placeholders.upload_logo'))
                            ->loadingIndicatorPosition('left')
                            ->panelAspectRatio('6:7')
                            ->panelLayout('integrated')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->uploadProgressIndicatorPosition('left'),

                        Grid::make()
                            ->schema([
                                TextInput::make('code')
                                    ->placeholder(__('app.placeholders.member_code'))
                                    ->label(__('app.fields.member_code'))
                                    ->required()
                                    ->readOnly()
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(fn (Get $get): string => Helpers::generateLastNumber(
                                        'member',
                                        Member::class,
                                        null,
                                        'code'
                                    )),
                                TextInput::make('name')
                                    ->label(__('app.fields.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder(__('app.placeholders.example_full_name'))
                                    ->columnSpan(2),
                                TextInput::make('email')
                                    ->label(__('app.fields.email'))
                                    ->email()
                                    ->live()
                                    ->maxLength(255)
                                    ->required()
                                    ->placeholder(__('app.placeholders.example_email'))
                                    ->unique('members', 'email', ignoreRecord: true),
                                TextInput::make('contact')
                                    ->label(__('app.fields.contact'))
                                    ->tel()
                                    ->placeholder(__('app.placeholders.example_phone'))
                                    ->maxLength(20)
                                    ->regex('/^\+?[0-9\s\-\(\)]+$/') // Allows +, digits, spaces, dashes, and parentheses
                                    ->required()
                                    ->hintIcon('heroicon-m-question-mark-circle')
                                    ->hintIconTooltip(__('app.help.phone_format')),
                                TextInput::make('emergency_contact')
                                    ->label(__('app.fields.emergency_contact'))
                                    ->tel()
                                    ->placeholder(__('app.placeholders.example_phone'))
                                    ->maxLength(20)
                                    ->regex('/^\+?[0-9\s\-\(\)]+$/') // Allows +, digits, spaces, dashes, and parentheses
                                    ->hintIcon('heroicon-m-question-mark-circle')
                                    ->hintIconTooltip(__('app.help.phone_format')),
                                Select::make('gender')
                                    ->options([
                                        'male' => __('app.options.gender.male'),
                                        'female' => __('app.options.gender.female'),
                                        'other' => __('app.options.gender.other'),
                                    ])->default('male')
                                    ->label(__('app.fields.gender'))
                                    ->selectablePlaceholder(false)
                                    ->required(),
                                DatePicker::make('dob')
                                    ->required()
                                    ->label(__('app.fields.dob'))
                                    ->placeholder(__('app.placeholders.date_example')),
                                TextInput::make('health_issue')
                                    ->label(__('app.fields.health_issues'))
                                    ->maxLength(500)
                                    ->placeholder(__('app.placeholders.health_issues')),
                                Select::make('source')
                                    ->options([
                                        'promotions' => __('app.options.source.promotions'),
                                        'word_of_mouth' => __('app.options.source.word_of_mouth'),
                                        'others' => __('app.options.source.others'),
                                    ])->default('promotions')
                                    ->label(__('app.fields.source'))
                                    ->selectablePlaceholder(false),
                                Select::make('goal')
                                    ->options([
                                        'fitness' => __('app.options.goal.fitness'),
                                        'body_building' => __('app.options.goal.body_building'),
                                        'fatloss' => __('app.options.goal.fatloss'),
                                        'weightgain' => __('app.options.goal.weightgain'),
                                        'others' => __('app.options.goal.others'),
                                    ])->default('fitness')
                                    ->label(__('app.fields.goal'))
                                    ->selectablePlaceholder(false),
                            ])->columns(3)->columnSpan(3),
                    ])->columns(4),
                Section::make(__('app.ui.location'))
                    ->columns(2)
                    ->schema([
                        Textarea::make('address')
                            ->label(__('app.fields.address'))
                            ->required()
                            ->rows(5)
                            ->placeholder(__('app.placeholders.address_example')),
                        Group::make()
                            ->columns(2)
                            ->schema([
                                Select::make('country')
                                    ->label(__('app.fields.country'))
                                    ->placeholder(__('app.placeholders.select_country'))
                                    ->options(Helpers::getCountries())
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set): array => [
                                        $set('state', null),
                                        $set('city', null),
                                    ]),
                                Select::make('state')
                                    ->label(__('app.fields.state'))
                                    ->placeholder(__('app.placeholders.select_state'))
                                    ->options(fn ($get): array => Helpers::getStates($get('country')))
                                    ->reactive(),
                                Select::make('city')
                                    ->label(__('app.fields.city'))
                                    ->placeholder(__('app.placeholders.select_city'))
                                    ->options(fn ($get): array => Helpers::getCities($get('state')))
                                    ->reactive(),
                                TextInput::make('pincode')
                                    ->label(__('app.fields.pincode'))
                                    ->numeric()
                                    ->required()
                                    ->placeholder(__('app.placeholders.pincode')),
                            ]),
                    ]),
                Section::make(__('app.titles.subscription_and_invoice'))
                    ->visibleOn('create')
                    ->schema([
                        Repeater::make('subscriptions')
                            ->relationship('subscriptions')
                            ->itemLabel('')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->maxItems(1)
                            ->deletable(false)
                            ->extraAttributes(['class' => 'rmv_rept-space'])
                            ->columns(3)
                            ->schema(fn (HasSchemas&Component $livewire): array => SubscriptionForm::configure(Schema::make($livewire))
                                ->getComponents(withActions: false)),
                    ]),
            ]);
    }
}
