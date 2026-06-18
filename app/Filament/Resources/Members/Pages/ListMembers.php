<?php

namespace App\Filament\Resources\Members\Pages;

use App\Enums\Status;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Helpers\Helpers;
use App\Models\Member;
use App\Services\Members\MemberOnboardingService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_member')
                ->label(__('app.actions.new', ['resource' => MemberResource::getModelLabel()]))
                ->icon('heroicon-m-plus')
                ->hidden(! Member::exists())
                ->modalWidth('7xl')
                ->modalHeading(__('app.actions.new', ['resource' => MemberResource::getModelLabel()]))
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
                ->action(function (array $data, Component $livewire): void {
                    $member = app(MemberOnboardingService::class)->create($data);

                    Notification::make()
                        ->title(__('app.notifications.member_created'))
                        ->body(__('app.enquiry_wizard.success', ['name' => $member->name]))
                        ->success()
                        ->send();

                    $livewire->redirect(MemberResource::getUrl('view', ['record' => $member]));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('app.common.all')),
            'active' => Tab::make(__('app.status.active'))
                ->badge(Member::query()->where('status', 'active')->count())
                ->badgeColor(Status::Active->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'active')),
            'inactive' => Tab::make(__('app.status.inactive'))
                ->badge(Member::query()->where('status', 'inactive')->count())
                ->badgeColor(Status::Inactive->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'inactive')),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.memberships'),
            MemberResource::getUrl('index') => MemberResource::getNavigationLabel(),
        ];
    }
}
