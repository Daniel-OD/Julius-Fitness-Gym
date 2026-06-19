<?php

namespace App\Filament\Resources\CheckIns;

use App\Enums\CheckInStatus;
use App\Filament\Resources\CheckIns\Pages\ListCheckIns;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Subscription;
use App\Services\CheckIns\CheckInService;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CheckInResource extends Resource
{
    protected static ?string $model = CheckIn::class;

    public static function getModelLabel(): string
    {
        return __('app.checkins.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('app.checkins.plural');
    }

    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    /**
     * No inline form — check-ins are recorded via QR scan or the manual
     * check-in toolbar action.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    /**
     * Resolve the member's currently active subscription, if any.
     */
    protected static function isOfficePanel(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'office';
    }

    protected static function activeSubscriptionFor(int $memberId): ?Subscription
    {
        $today = CarbonImmutable::today(AppConfig::timezone())->toDateString();

        return Subscription::query()
            ->where('member_id', $memberId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereNotIn('status', ['cancelled', 'renewed'])
            ->latest('end_date')
            ->first();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.name')
                    ->label(__('app.fields.member'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('checked_in_at')
                    ->label(__('app.checkins.checked_in'))
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('checked_out_at')
                    ->label(__('app.checkins.checked_out'))
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('duration')
                    ->label(__('app.checkins.duration'))
                    ->state(fn (CheckIn $record): string => $record->status !== CheckInStatus::Blocked && $record->durationMinutes() !== null
                        ? __('app.checkins.minutes', ['min' => $record->durationMinutes()])
                        : '—')
                    ->sortable(false),
                TextColumn::make('status')
                    ->label(__('app.checkins.status'))
                    ->badge()
                    ->description(fn (CheckIn $record): ?string => $record->denied_reason
                        ? __('app.checkins.denied_reasons.'.$record->denied_reason)
                        : null),
                TextColumn::make('subscription.plan.name')
                    ->label(__('app.fields.plan'))
                    ->placeholder('—'),
                TextColumn::make('method')
                    ->label(__('app.fields.method'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'qr' ? 'info' : 'gray'),
            ])
            ->defaultSort('checked_in_at', 'desc')
            ->filters([
                SelectFilter::make('method')
                    ->label(__('app.fields.method'))
                    ->options(['qr' => 'QR', 'manual' => 'Manual']),
                SelectFilter::make('status')
                    ->label(__('app.checkins.status'))
                    ->options(CheckInStatus::class),
                Filter::make('checked_in_between')
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('app.checkins.from')),
                        DatePicker::make('until')
                            ->label(__('app.checkins.until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('checked_in_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('checked_in_at', '<=', $date));
                    }),
            ])
            ->headerActions([
                Action::make('exportCsv')
                    ->label(__('app.checkins.export_csv'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (HasTable $livewire): StreamedResponse {
                        $query = ($livewire->getFilteredTableQuery() ?? CheckIn::query())
                            ->with(['member', 'subscription.plan'])
                            ->reorder();

                        return response()->streamDownload(function () use ($query): void {
                            $handle = fopen('php://output', 'w');

                            fputcsv($handle, [
                                __('app.fields.member'),
                                __('app.checkins.checked_in'),
                                __('app.checkins.checked_out'),
                                __('app.checkins.status'),
                                __('app.checkins.denied_reason'),
                                __('app.fields.plan'),
                                __('app.fields.method'),
                            ]);

                            foreach ($query->lazyById(200) as $checkIn) {
                                /** @var CheckIn $checkIn */
                                fputcsv($handle, [
                                    $checkIn->member?->name ?? '—',
                                    $checkIn->checked_in_at->format('Y-m-d H:i'),
                                    $checkIn->checked_out_at?->format('Y-m-d H:i') ?? '',
                                    $checkIn->status->getLabel(),
                                    $checkIn->denied_reason
                                        ? __('app.checkins.denied_reasons.'.$checkIn->denied_reason)
                                        : '',
                                    $checkIn->subscription?->plan?->name ?? '',
                                    $checkIn->method,
                                ]);
                            }

                            fclose($handle);
                        }, 'check-ins-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv']);
                    }),
                Action::make('manualCheckIn')
                    ->label(__('app.checkins.manual_checkin'))
                    ->icon('heroicon-o-check-circle')
                    ->button()
                    ->size(static::isOfficePanel() ? Size::Large : Size::Medium)
                    ->color(static::isOfficePanel() ? 'primary' : 'gray')
                    ->extraAttributes(static::isOfficePanel() ? ['class' => 'office-manual-checkin-btn'] : [])
                    ->schema([
                        Select::make('member_id')
                            ->label(__('app.fields.member'))
                            ->options(fn (): array => Member::query()
                                ->whereHas('subscriptions', function ($query): void {
                                    $today = CarbonImmutable::today(AppConfig::timezone())->toDateString();
                                    $query->whereDate('start_date', '<=', $today)
                                        ->whereDate('end_date', '>=', $today)
                                        ->whereNotIn('status', ['cancelled', 'renewed']);
                                })
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->live()
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalIconColor('success')
                    ->modalHeading(__('app.checkins.confirm_checkin_heading'))
                    ->modalDescription(function (array $data): HtmlString|string {
                        $memberId = (int) ($data['member_id'] ?? 0);

                        if ($memberId === 0) {
                            return '';
                        }

                        $member = Member::query()->find($memberId);

                        if ($member === null) {
                            return '';
                        }

                        $subscription = static::activeSubscriptionFor($memberId);
                        $planName = $subscription?->plan?->name ?? __('app.members.qr.no_subscription');

                        return new HtmlString(
                            '<ul class="office-confirm-list">'
                            .'<li><strong>'.e(__('app.fields.member')).':</strong> '.e($member->name).'</li>'
                            .'<li><strong>'.e(__('app.fields.plan')).':</strong> '.e($planName).'</li>'
                            .'</ul>'
                        );
                    })
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

                        CheckIn::create([
                            'member_id' => $memberId,
                            'subscription_id' => static::activeSubscriptionFor($memberId)?->id,
                            'checked_in_at' => now(),
                            'method' => 'manual',
                        ]);

                        Notification::make()
                            ->title(__('app.checkins.manual_checkin_done_for', ['name' => $member->name]))
                            ->body(static::activeSubscriptionFor($memberId)?->plan?->name ?? __('app.members.qr.no_subscription'))
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('checkOut')
                    ->label(__('app.checkins.check_out'))
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->color('danger')
                    ->button()
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-clock')
                    ->modalIconColor('danger')
                    ->modalHeading(__('app.checkins.confirm_checkout_heading'))
                    ->modalDescription(function (CheckIn $record): HtmlString {
                        $time = now()->timezone(AppConfig::timezone())->translatedFormat('d M Y, H:i');

                        return new HtmlString(
                            '<ul class="office-confirm-list">'
                            .'<li><strong>'.e(__('app.fields.member')).':</strong> '.e($record->member?->name ?? '—').'</li>'
                            .'<li><strong>'.e(__('app.checkins.checkout_time')).':</strong> '.e($time).'</li>'
                            .'</ul>'
                        );
                    })
                    ->modalSubmitActionLabel(__('app.checkins.confirm_checkout_submit'))
                    ->visible(fn (CheckIn $record): bool => $record->checked_out_at === null
                        && $record->status !== CheckInStatus::Blocked)
                    ->action(function (CheckIn $record): void {
                        $record->update(['checked_out_at' => now()]);

                        Notification::make()
                            ->title(__('app.checkins.check_out_done_for', [
                                'name' => $record->member?->name ?? '—',
                            ]))
                            ->body(now()->timezone(AppConfig::timezone())->translatedFormat('d M Y, H:i'))
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCheckIns::route('/'),
        ];
    }
}
