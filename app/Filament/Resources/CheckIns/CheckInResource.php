<?php

namespace App\Filament\Resources\CheckIns;

use App\Filament\Resources\CheckIns\Pages\ListCheckIns;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Subscription;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

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
                    ->state(fn (CheckIn $record): string => $record->durationMinutes() !== null
                        ? __('app.checkins.minutes', ['min' => $record->durationMinutes()])
                        : '—')
                    ->sortable(false),
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
                SelectFilter::make('period')
                    ->label(__('app.checkins.today'))
                    ->options([
                        'today' => __('app.checkins.today'),
                        'this_week' => __('app.checkins.this_week'),
                        'this_month' => __('app.checkins.this_month'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'today' => $query->whereDate('checked_in_at', today()),
                            'this_week' => $query->whereBetween('checked_in_at', [now()->startOfWeek(), now()->endOfWeek()]),
                            'this_month' => $query->whereBetween('checked_in_at', [now()->startOfMonth(), now()->endOfMonth()]),
                            default => $query,
                        };
                    }),
                SelectFilter::make('method')
                    ->label(__('app.fields.method'))
                    ->options(['qr' => 'QR', 'manual' => 'Manual']),
            ])
            ->headerActions([
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
                    ->visible(fn (CheckIn $record): bool => $record->checked_out_at === null)
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
