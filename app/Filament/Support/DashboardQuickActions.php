<?php

namespace App\Filament\Support;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Subscription;
use App\Services\CheckIns\CheckInService;
use App\Services\Members\MemberOnboardingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Wizard\Step;
use Livewire\Component;

final class DashboardQuickActions
{
    /**
     * @return array<int, Action>
     */
    public static function make(Component $livewire): array
    {
        return [
            Action::make('new_member')
                ->label(__('app.dashboard.quick_actions.new_member'))
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->link()
                ->modalWidth('7xl')
                ->extraModalWindowAttributes(['class' => 'jf-onboarding-wizard'])
                ->modalHeading(__('app.actions.new', ['resource' => __('app.resources.members.singular')]))
                ->steps([
                    Step::make(__('app.enquiry_wizard.step_member'))
                        ->icon('heroicon-o-user')
                        ->columns(1)
                        ->schema(MemberForm::onboardingMemberStep()),
                    Step::make(__('app.enquiry_wizard.step_subscription'))
                        ->icon('heroicon-o-credit-card')
                        ->schema(SubscriptionForm::onboardingSubscriptionStep()),
                ])
                ->action(function (array $data) use ($livewire): void {
                    $member = app(MemberOnboardingService::class)->create($data);

                    Notification::make()
                        ->title(__('app.notifications.member_created'))
                        ->body(__('app.enquiry_wizard.success', ['name' => $member->name]))
                        ->success()
                        ->send();

                    $livewire->redirect(MemberResource::getUrl('view', ['record' => $member]));
                }),

            Action::make('manual_checkin')
                ->label(__('app.dashboard.quick_actions.manual_checkin'))
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->link()
                ->schema([
                    Select::make('member_id')
                        ->label(__('app.fields.member'))
                        ->options(fn (): array => Member::query()
                            ->whereHas('subscriptions', function ($query): void {
                                $today = now()->toDateString();
                                $query->whereDate('start_date', '<=', $today)
                                    ->whereDate('end_date', '>=', $today)
                                    ->whereNotIn('status', ['cancelled', 'renewed']);
                            })
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->requiresConfirmation()
                ->modalIcon('heroicon-o-qr-code')
                ->modalIconColor('success')
                ->modalHeading(__('app.checkins.confirm_checkin_heading'))
                ->modalSubmitActionLabel(__('app.checkins.confirm_checkin_submit'))
                ->action(function (array $data): void {
                    $memberId = (int) $data['member_id'];
                    $member = Member::query()->findOrFail($memberId);

                    if (app(CheckInService::class)->hasOpenSession($memberId)) {
                        Notification::make()
                            ->title(__('app.checkins.already_present_title'))
                            ->body(__('app.checkins.already_present_body', ['name' => $member->name]))
                            ->danger()
                            ->send();

                        return;
                    }

                    $subscriptionId = Subscription::query()
                        ->where('member_id', $memberId)
                        ->whereDate('start_date', '<=', today())
                        ->whereDate('end_date', '>=', today())
                        ->whereNotIn('status', ['cancelled', 'renewed'])
                        ->latest('end_date')
                        ->value('id');

                    CheckIn::create([
                        'member_id' => $memberId,
                        'subscription_id' => $subscriptionId,
                        'checked_in_at' => now(),
                        'method' => 'manual',
                    ]);

                    Notification::make()
                        ->title(__('app.checkins.manual_checkin_done_for', ['name' => $member->name]))
                        ->success()
                        ->send();
                }),

            Action::make('new_lead')
                ->label(__('app.dashboard.quick_actions.new_lead'))
                ->icon('heroicon-o-chat-bubble-left')
                ->color('gray')
                ->link()
                ->url(EnquiryResource::getUrl('create')),
        ];
    }
}
