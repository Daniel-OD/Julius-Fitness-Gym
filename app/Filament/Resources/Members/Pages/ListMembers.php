<?php

namespace App\Filament\Resources\Members\Pages;

use App\Enums\Status;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Models\Member;
use App\Services\Members\MemberOnboardingService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Database\Eloquent\Builder;
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
                ->modalWidth('7xl')
                ->extraModalWindowAttributes(['class' => 'jf-onboarding-wizard'])
                ->modalHeading(__('app.actions.new', ['resource' => MemberResource::getModelLabel()]))
                ->steps([
                    Step::make(__('app.enquiry_wizard.step_member'))
                        ->icon('heroicon-o-user')
                        ->columns(1)
                        ->schema(MemberForm::onboardingMemberStep()),
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
