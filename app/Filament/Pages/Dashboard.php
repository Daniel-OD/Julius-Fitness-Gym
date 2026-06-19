<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Filament\Resources\Subscriptions\Schemas\SubscriptionForm;
use App\Filament\Widgets\Analytics\AtRiskMembersTableWidget;
use App\Filament\Widgets\Analytics\CashflowTrendChartWidget;
use App\Filament\Widgets\Analytics\ExpenseCategoriesDoughnutChartWidget;
use App\Filament\Widgets\Analytics\FinancialMetricsWidget;
use App\Filament\Widgets\Analytics\MembershipMetricsWidget;
use App\Filament\Widgets\Analytics\MembershipOverviewSubscriptionsTableWidget;
use App\Filament\Widgets\Analytics\RecentTransactionsTableWidget;
use App\Filament\Widgets\Billing\UninvoicedSubscriptionsTableWidget;
use App\Filament\Widgets\GymOverviewStatsWidget;
use App\Filament\Widgets\TodayCheckinsStatsWidget;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Subscription;
use App\Services\CheckIns\CheckInService;
use App\Services\Members\MemberOnboardingService;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard\Concerns\HasFilters;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component as LivewireComponent;

/**
 * Main application dashboard.
 *
 * This dashboard hosts business analytics widgets (collected-based) and provides
 * a time-range filter that widgets can read via `InteractsWithPageFilters`.
 */
class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFilters;

    protected static string $routePath = 'dashboard';

    protected static ?string $title = null;

    /**
     * Get the dashboard page title.
     */
    public function getTitle(): string
    {
        return __('app.dashboard.title');
    }

    /**
     * Get the dashboard navigation label.
     */
    public static function getNavigationLabel(): string
    {
        return __('app.navigation.dashboard');
    }

    /**
     * Render a custom header that includes a real select field for the date range.
     *
     * For a select-style control in the top-right, we render a custom header view
     * that binds directly to this Livewire component.
     */
    public function getHeader(): ?View
    {
        return view('filament.pages.dashboard-header');
    }

    /**
     * Quick-action buttons rendered in the dashboard header.
     *
     * @return array<int, mixed>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_member')
                ->label(__('app.dashboard.quick_actions.new_member'))
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->button()
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
                ->action(function (array $data, LivewireComponent $livewire): void {
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
                ->button()
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
                ->outlined()
                ->button()
                ->url(EnquiryResource::getUrl('create')),
        ];
    }

    /**
     * Dashboard header form schema (date range controls).
     *
     * We use Filament form components (non-native select) instead of raw HTML so
     * the control feels consistent with the rest of the admin UI.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->inline()
            ->statePath('filters')
            ->extraAttributes([
                'class' => 'flex flex-wrap items-center justify-end gap-2',
            ])
            ->components([
                Select::make('period')
                    ->hiddenLabel()
                    ->native(false)
                    ->prefixIcon('heroicon-o-calendar-days')
                    ->options([
                        '7days' => __('app.dashboard.filters.periods.7days'),
                        '30days' => __('app.dashboard.filters.periods.30days'),
                        'month' => __('app.dashboard.filters.periods.month'),
                        'quarter' => __('app.dashboard.filters.periods.quarter'),
                        'year' => __('app.dashboard.filters.periods.year'),
                        'custom' => __('app.dashboard.filters.periods.custom'),
                    ])
                    ->default('7days')
                    ->live()
                    ->afterStateUpdated(function (mixed $state): void {
                        if (in_array($state, ['7days', '30days', 'month', 'quarter', 'year', 'custom'], true)) {
                            $this->setPeriod($state);
                        }
                    })
                    ->grow(false)
                    ->extraFieldWrapperAttributes([
                        'class' => 'w-full sm:w-56',
                    ]),
                DatePicker::make('startDate')
                    ->hiddenLabel()
                    ->placeholder(__('app.dashboard.filters.start'))
                    ->visible(fn (Get $get): bool => $get('period') === 'custom')
                    ->live()
                    ->grow(false)
                    ->extraFieldWrapperAttributes([
                        'class' => 'w-full sm:w-40',
                    ]),
                DatePicker::make('endDate')
                    ->hiddenLabel()
                    ->placeholder(__('app.dashboard.filters.end'))
                    ->visible(fn (Get $get): bool => $get('period') === 'custom')
                    ->live()
                    ->grow(false)
                    ->extraFieldWrapperAttributes([
                        'class' => 'w-full sm:w-40',
                    ]),
            ]);
    }

    /**
     * Get the responsive dashboard column layout.
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 4,
        ];
    }

    /**
     * Render dashboard widgets in grouped layout blocks.
     *
     * We group Financial + Spending Overview together so the two cards always
     * sit side-by-side on larger screens.
     */
    public function getWidgetsContentComponent(): Component
    {
        $columns = $this->getColumns();

        return Grid::make(1)->schema([
            Grid::make($columns)->schema([
                ...$this->getWidgetsSchemaComponents([
                    TodayCheckinsStatsWidget::class,
                ]),
            ]),
            Grid::make($columns)->schema([
                ...$this->getWidgetsSchemaComponents([
                    GymOverviewStatsWidget::class,
                ]),
            ]),
            Grid::make($columns)->schema([
                ...$this->getWidgetsSchemaComponents([
                    MembershipMetricsWidget::class,
                ]),
            ]),
            Grid::make($columns)->schema(
                $this->getWidgetsSchemaComponents([
                    FinancialMetricsWidget::class,
                    ExpenseCategoriesDoughnutChartWidget::class,
                ]),
            ),
            Grid::make($columns)->schema([
                ...$this->getWidgetsSchemaComponents([
                    MembershipOverviewSubscriptionsTableWidget::class,
                    RecentTransactionsTableWidget::class,
                ]),
            ]),
            Grid::make(1)->schema([
                ...$this->getWidgetsSchemaComponents([
                    UninvoicedSubscriptionsTableWidget::class,
                ]),
            ]),
            Grid::make(1)->schema([
                ...$this->getWidgetsSchemaComponents([
                    AtRiskMembersTableWidget::class,
                ]),
            ]),
            Grid::make($columns)->schema(
                $this->getWidgetsSchemaComponents([
                    CashflowTrendChartWidget::class,
                ]),
            ),
        ]);
    }

    /**
     * Initialize the filters for first-time visits where there is no persisted state.
     *
     * This is called from the header view using `wire:init`, so the select field
     * always has a valid value without relying on mount-ordering details.
     */
    public function ensureDefaultFilters(): void
    {
        $period = is_string($this->filters['period'] ?? null) ? $this->filters['period'] : '';

        if ($period === 'ytd') {
            $this->filters['period'] = 'year';
            $this->updatedFilters();

            return;
        }

        if ($period !== '') {
            return;
        }

        $this->applyPresetRange('7days');
    }

    /**
     * Handle selecting a period from the dashboard header select.
     *
     * @param  '7days'|'30days'|'month'|'quarter'|'year'|'custom'  $period
     */
    public function setPeriod(string $period): void
    {
        if ($period === 'custom') {
            $today = CarbonImmutable::today(AppConfig::timezone());
            $startDate = is_string($this->filters['startDate'] ?? null) ? $this->filters['startDate'] : null;
            $endDate = is_string($this->filters['endDate'] ?? null) ? $this->filters['endDate'] : null;

            $this->filters = [
                ...($this->filters ?? []),
                'period' => 'custom',
                'startDate' => $startDate ?: $today->subDays(6)->toDateString(),
                'endDate' => $endDate ?: $today->toDateString(),
            ];

            $this->updatedFilters();

            return;
        }

        $this->applyPresetRange($period);
    }

    /**
     * Apply the currently selected custom range (start & end dates).
     */
    public function applyCustomRangeFromFilters(): void
    {
        $startDate = is_string($this->filters['startDate'] ?? null) ? $this->filters['startDate'] : '';
        $endDate = is_string($this->filters['endDate'] ?? null) ? $this->filters['endDate'] : '';

        if (($startDate === '') || ($endDate === '')) {
            return;
        }

        $this->applyCustomRange($startDate, $endDate);
    }

    /**
     * Apply a preset range to the dashboard filters.
     *
     * @param  '7days'|'30days'|'month'|'quarter'|'year'  $preset
     */
    private function applyPresetRange(string $preset): void
    {
        $today = CarbonImmutable::today(AppConfig::timezone());

        [$start, $end, $period] = match ($preset) {
            '30days' => [$today->subDays(29), $today, '30days'],
            'quarter' => [$today->startOfQuarter(), $today, 'quarter'],
            'year' => [$today->startOfYear(), $today, 'year'],
            default => [$today->subDays(6), $today, '7days'],
        };

        if ($preset === 'month') {
            $start = $today->startOfMonth();
            $end = $today;
            $period = 'month';
        }

        $this->filters = [
            'period' => $period,
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
        ];

        $this->updatedFilters();
    }

    /**
     * Apply a custom range to the dashboard filters.
     */
    private function applyCustomRange(string $startDate, string $endDate): void
    {
        $timezone = AppConfig::timezone();

        $start = CarbonImmutable::parse($startDate, $timezone)->startOfDay();
        $end = CarbonImmutable::parse($endDate, $timezone)->endOfDay();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->startOfDay(), $start->endOfDay()];
        }

        $this->filters = [
            'period' => 'custom',
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
        ];

        $this->updatedFilters();
    }
}
