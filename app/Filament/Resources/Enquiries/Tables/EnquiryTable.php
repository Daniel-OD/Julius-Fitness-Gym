<?php

namespace App\Filament\Resources\Enquiries\Tables;

use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Helpers\Helpers;
use App\Models\Enquiry;
use App\Services\Members\MemberOnboardingService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EnquiryTable
{
    /**
     * Configure the enquiry table schema.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->toggleable(isToggledHiddenByDefault: true)->label(__('app.fields.id')),
                TextColumn::make('name')->searchable()->sortable()->label(__('app.fields.name')),
                TextColumn::make('email')->searchable()->toggleable(isToggledHiddenByDefault: false)->label(__('app.fields.email')),
                TextColumn::make('contact')->toggleable(isToggledHiddenByDefault: true)->label(__('app.fields.contact')),
                TextColumn::make('date')->sortable()->date('d-m-Y')->toggleable(isToggledHiddenByDefault: true)->label(__('app.fields.date')),
                TextColumn::make('start_by')->date('d-m-Y')->toggleable(isToggledHiddenByDefault: true)->label(__('app.fields.start_by')),
                TextColumn::make('status')
                    ->badge()
                    ->label(__('app.fields.status'))
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('id', 'desc')
            ->emptyStateIcon('heroicon-o-phone')
            ->emptyStateHeading(function ($livewire): string {
                $dates = $livewire->getTableFilterState('date') ?? [];
                [$from, $to] = [$dates['date_from'] ?? null, $dates['date_to'] ?? null];
                $records = (string) __('app.resources.enquiries.plural');
                $tab = (string) ($livewire->activeTab ?? 'all');
                $status = $tab !== 'all' ? (string) __('app.status.'.$tab) : null;

                if (! $from && ! $to) {
                    return $status
                        ? __('app.empty.no_status_records', ['status' => $status, 'records' => $records])
                        : __('app.empty.no_records', ['records' => $records]);
                }

                if ($tab === 'all') {
                    return __('app.empty.no_records_in_range', ['records' => $records]);
                }

                return Enquiry::where('status', $tab)->exists()
                    ? __('app.empty.no_status_records_in_range', ['status' => $status, 'records' => $records])
                    : __('app.empty.no_status_records', ['status' => $status, 'records' => $records]);
            })
            ->emptyStateDescription(function ($livewire): string {
                $dates = $livewire->getTableFilterState('date') ?? [];
                [$fromRaw, $toRaw] = [$dates['date_from'] ?? null, $dates['date_to'] ?? null];
                $records = (string) __('app.resources.enquiries.plural');
                $record = (string) __('app.resources.enquiries.singular');
                $tab = (string) ($livewire->activeTab ?? 'all');
                $status = $tab !== 'all' ? (string) __('app.status.'.$tab) : null;

                if (! $fromRaw && ! $toRaw) {
                    return $status
                        ? __('app.empty.no_records_marked_as', ['records' => $records, 'status' => $status])
                        : __('app.empty.create_to_get_started', ['resource' => $record]);
                }

                $from = $fromRaw ? Carbon::parse($fromRaw)->format('d-m-Y') : (string) __('app.common.the_beginning');
                $to = $toRaw ? Carbon::parse($toRaw)->format('d-m-Y') : (string) __('app.common.today');

                if ($tab === 'all') {
                    return __('app.empty.found_none_between', ['records' => $records, 'from' => $from, 'to' => $to]);
                }

                if (! Enquiry::where('status', $tab)->exists()) {
                    return __('app.empty.no_records_marked_as', ['records' => $records, 'status' => $status]);
                }

                return __('app.empty.found_none_status_between', ['status' => $status, 'records' => $records, 'from' => $from, 'to' => $to]);
            })
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label(__('app.actions.new', ['resource' => __('app.resources.enquiries.singular')]))
                    ->hidden(fn (): bool => Enquiry::exists()),
            ])
            ->filters([
                TrashedFilter::make(),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('date_from')->label(__('app.fields.date_from')),
                        DatePicker::make('date_to')->label(__('app.fields.date_to')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ActionGroup::make([
                        Action::make('heading_actions')
                            ->label(__('app.fields.status'))
                            ->disabled()
                            ->visible(fn ($record): bool => in_array($record->status?->value, ['lead'], true))
                            ->color('gray'),
                        Action::make('convert_to_member')
                            ->label(__('app.actions.convert_to_member'))
                            ->icon('heroicon-m-arrows-right-left')
                            ->color('success')
                            ->visible(fn (Enquiry $record): bool => $record->status?->value === 'lead')
                            ->modalWidth('7xl')
                            ->modalHeading(__('app.enquiry_wizard.modal_heading'))
                            ->steps([
                                Step::make(__('app.enquiry_wizard.step_member'))
                                    ->icon('heroicon-o-user')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('app.fields.name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
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
                                        Select::make('gender')
                                            ->label(__('app.fields.gender'))
                                            ->options([
                                                'male' => __('app.options.gender.male'),
                                                'female' => __('app.options.gender.female'),
                                                'other' => __('app.options.gender.other'),
                                            ])
                                            ->default('male')
                                            ->required()
                                            ->selectablePlaceholder(false),
                                        DatePicker::make('dob')
                                            ->label(__('app.fields.dob'))
                                            ->required(),
                                        Textarea::make('address')
                                            ->label(__('app.fields.address'))
                                            ->required()
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Select::make('country')
                                            ->label(__('app.fields.country'))
                                            ->options(Helpers::getCountries())
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn (Set $set) => [$set('state', null), $set('city', null)]),
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
                                            ->numeric()
                                            ->required(),
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
                                Step::make(__('app.enquiry_wizard.step_subscription'))
                                    ->icon('heroicon-o-credit-card')
                                    ->schema(SubscriptionForm::onboardingSubscriptionStep()),
                            ])
                            ->fillForm(fn (Enquiry $record): array => [
                                'name' => $record->name,
                                'email' => $record->email,
                                'contact' => $record->contact,
                                'dob' => $record->dob,
                                'gender' => $record->gender ?? 'male',
                                'address' => $record->address,
                                'country' => $record->country,
                                'state' => $record->state,
                                'city' => $record->city,
                                'pincode' => $record->pincode,
                                'source' => $record->source,
                                'goal' => $record->goal,
                            ])
                            ->action(function (Enquiry $record, array $data, Component $livewire): void {
                                $member = app(MemberOnboardingService::class)->createFromEnquiry($record, $data);

                                Notification::make()
                                    ->title(__('app.notifications.member_created'))
                                    ->body(__('app.enquiry_wizard.success', ['name' => $member->name]))
                                    ->success()
                                    ->send();

                                $livewire->redirect(MemberResource::getUrl('view', ['record' => $member]));
                            }),
                        Action::make('mark_as_lost')
                            ->label(__('app.actions.mark_as_lost'))
                            ->icon('heroicon-m-x-circle')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->action(fn (Enquiry $record) => tap($record, function ($record) {
                                $record->update(['status' => 'lost']);
                                Notification::make()
                                    ->title(__('app.notifications.enquiry_marked_as_lost'))
                                    ->success()
                                    ->icon('heroicon-m-no-symbol')
                                    ->iconColor('danger')
                                    ->send();
                            }))
                            ->visible(fn ($record): bool => $record->status?->value === 'lead'),
                    ])->dropdown(false),
                    ActionGroup::make([
                        Action::make('heading_actions')
                            ->label(__('app.actions.record_actions'))
                            ->disabled()
                            ->color('gray'),
                        EditAction::make()->hiddenLabel(),
                        DeleteAction::make()
                            ->hiddenLabel(),
                    ])->dropdown(false),
                ]),
            ])->recordUrl(fn ($record): string => route('filament.admin.resources.enquiries.view', $record->id))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
