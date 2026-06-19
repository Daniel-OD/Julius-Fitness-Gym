<?php

namespace App\Filament\Resources\Enquiries\Pages;

use App\Enums\Status;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Models\Enquiry;
use App\Services\Members\MemberOnboardingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Wizard\Step;
use Livewire\Component;

/**
 * @property-read Enquiry $record
 */
class ViewEnquiry extends ViewRecord
{
    protected static string $resource = EnquiryResource::class;

    public function getTitle(): string
    {
        return __('app.titles.record', [
            'resource' => EnquiryResource::getModelLabel(),
            'name' => $this->record->name,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
            Action::make('convert_to_member')
                ->label(__('app.actions.convert_to_member'))
                ->icon('heroicon-s-arrows-right-left')
                ->color('success')
                ->visible(fn (Enquiry $record) => $record->status === Status::Lead)
                ->modalWidth('7xl')
                ->extraModalWindowAttributes(['class' => 'jf-onboarding-wizard'])
                ->modalHeading(__('app.enquiry_wizard.modal_heading'))
                ->steps([
                    Step::make(__('app.enquiry_wizard.step_member'))
                        ->icon('heroicon-o-user')
                        ->columns(1)
                        ->schema(MemberForm::onboardingMemberStep()),
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
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.sales'),
            EnquiryResource::getUrl('index') => EnquiryResource::getNavigationLabel(),
            $this->record->name,
        ];
    }
}
