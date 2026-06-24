<?php

namespace App\Filament\Support;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Sales\SaleResource;
use App\Filament\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Product;
use App\Models\Subscription;
use App\Services\CheckIns\CheckInService;
use App\Services\Members\MemberOnboardingService;
use App\Services\Shop\SaleService;
use App\Support\Billing\PaymentMethod;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
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
                // removed ->link() so modal is opened via Livewire (no GET to Livewire internal routes)
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
                // removed ->link() so modal is opened via Livewire (no GET to Livewire internal routes)
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

            Action::make('new_sale')
                ->label(__('app.dashboard.quick_actions.new_sale'))
                ->icon('heroicon-o-shopping-bag')
                ->color('warning')
                ->link()
                ->modalWidth('3xl')
                ->modalHeading(__('app.shop.new_sale'))
                ->schema([
                    Select::make('member_id')
                        ->label(__('app.fields.member'))
                        ->options(fn (): array => Member::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->nullable(),
                    Select::make('payment_method')
                        ->label(__('app.fields.payment_method'))
                        ->options(PaymentMethod::options())
                        ->default('cash')
                        ->required()
                        ->native(false),
                    Repeater::make('items')
                        ->label(__('app.shop.sale_items'))
                        ->schema([
                            Select::make('product_id')
                                ->label(__('app.resources.products.singular'))
                                ->options(fn (): array => Product::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->required(),
                            Select::make('quantity')
                                ->label(__('app.shop.quantity'))
                                ->options(collect(range(1, 20))->mapWithKeys(fn (int $n): array => [$n => (string) $n])->all())
                                ->default(1)
                                ->required(),
                        ])
                        ->minItems(1)
                        ->defaultItems(1)
                        ->addActionLabel(__('app.shop.add_item'))
                        ->columns(2),
                ])
                ->action(function (array $data) use ($livewire): void {
                    try {
                        app(SaleService::class)->create([
                            'member_id' => $data['member_id'] ?? null,
                            'payment_method' => $data['payment_method'] ?? 'cash',
                            'items' => collect($data['items'] ?? [])
                                ->map(fn (array $item): array => [
                                    'product_id' => (int) $item['product_id'],
                                    'quantity' => (int) $item['quantity'],
                                ])
                                ->all(),
                        ], auth()->user());

                        Notification::make()
                            ->title(__('app.shop.sale_completed'))
                            ->success()
                            ->send();

                        $livewire->redirect(SaleResource::getUrl('index'));
                    } catch (\InvalidArgumentException $exception) {
                        Notification::make()
                            ->title(__('app.notifications.failed'))
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
