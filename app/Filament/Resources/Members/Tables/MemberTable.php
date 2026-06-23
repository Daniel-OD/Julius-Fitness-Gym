<?php

namespace App\Filament\Resources\Members\Tables;

use App\Enums\Status;
use App\Filament\Resources\Members\Actions\ResetMemberPasswordAction;
use App\Models\Member;
use App\Services\Email\MemberPortalEmailService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberTable
{
    /**
     * Configure the member table schema.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns());
    }

    private static function getColumns(): array
    {
        return [
            ImageColumn::make('photo')
                ->label('')
                ->disk('public')
                ->defaultImageUrl(fn (Member $record): string => 'https://ui-avatars.com/api/?background=ff5a1f&color=fff&name='.urlencode($record->name ?? ''))
                ->circular()
                ->size(40)
                ->grow(false),
            TextColumn::make('name')
                ->label(__('app.fields.member'))
                ->searchable(['name', 'code', 'email'])
                ->sortable()
                ->description(fn (Member $record): string => $record->code)
                ->weight(FontWeight::SemiBold)
                ->wrap()
                ->grow()
                ->extraCellAttributes(['class' => 'jf-member-identity-cell']),
            TextColumn::make('email')
                ->searchable()
                ->label(__('app.fields.email'))
                ->toggleable()
                ->wrap(),
            TextColumn::make('contact'),
        ];
    }
}
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('app.fields.contact'))
                    ->wrap(),
                TextColumn::make('gender')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('app.fields.gender'))
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'male' => __('app.options.gender.male'),
                        'female' => __('app.options.gender.female'),
                        'other' => __('app.options.gender.other'),
                        default => filled($state) ? ucfirst($state) : __('app.placeholders.dash'),
                    }),
                TextColumn::make('emergency_contact')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('app.fields.emergency_contact')),
                TextColumn::make('created_at')
                    ->sortable()
                    ->date('d-m-Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('app.fields.date')),
                TextColumn::make('status')
                    ->badge()
                    ->label(__('app.fields.status'))
                    ->formatStateUsing(fn (?Status $state): string => $state
                        ? __('app.status.'.$state->value)
                        : __('app.placeholders.dash'))
                    ->color(fn (?Status $state): string => $state?->getColor() ?? 'gray')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('app.fields.id')),
            ])
            ->extraAttributes(['class' => 'jf-members-table'])
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading(function ($livewire): string {
                $dates = $livewire->getTableFilterState('date') ?? [];
                [$from, $to] = [$dates['date_from'] ?? null, $dates['date_to'] ?? null];
                $records = (string) __('app.resources.members.plural');
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

                $base = __('app.empty.no_status_records', ['status' => $status, 'records' => $records]);

                return Member::where('status', $tab)->exists()
                    ? __('app.empty.no_status_records_in_range', ['status' => $status, 'records' => $records])
                    : $base;
            })
            ->emptyStateDescription(function ($livewire): string {
                $dates = $livewire->getTableFilterState('date') ?? [];
                [$fromRaw, $toRaw] = [$dates['date_from'] ?? null, $dates['date_to'] ?? null];
                $records = (string) __('app.resources.members.plural');
                $record = (string) __('app.resources.members.singular');
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

                if (! Member::where('status', $tab)->exists()) {
                    return __('app.empty.no_records_marked_as', ['records' => $records, 'status' => $status]);
                }

                return __('app.empty.found_none_status_between', ['status' => $status, 'records' => $records, 'from' => $from, 'to' => $to]);
            })
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label(__('app.actions.new', ['resource' => __('app.resources.members.singular')]))
                    ->hidden(fn (): bool => Member::exists()),
            ])
            ->filters([
                TrashedFilter::make(),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label(__('app.fields.date_from'))
                            ->native(false)
                            ->suffixIcon('heroicon-m-calendar-days'),
                        DatePicker::make('date_to')
                            ->label(__('app.fields.date_to'))
                            ->native(false)
                            ->suffixIcon('heroicon-m-calendar-days'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['date_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['date_to'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        )),
            ])
            ->recordActions([
                ActionGroup::make([
                    ActionGroup::make([
                        Action::make('heading_actions')
                            ->label(__('app.fields.status'))
                            ->disabled()
                            ->color('gray'),
                        Action::make('mark_as_active')
                            ->color('success')
                            ->label(__('app.actions.mark_as_active'))
                            ->requiresConfirmation()
                            ->action(fn (Member $record) => tap($record, function ($record): void {
                                $record->update(['status' => 'active']);
                                Notification::make()
                                    ->title(__('app.notifications.member_activated'))
                                    ->success()
                                    ->send();
                            }))
                            ->visible(fn (Member $record): bool => $record->status?->value === 'inactive'),
                        Action::make('mark_as_inactive')
                            ->color('danger')
                            ->label(__('app.actions.mark_as_inactive'))
                            ->requiresConfirmation()
                            ->action(fn (Member $record) => tap($record, function ($record): void {
                                $record->update(['status' => 'inactive']);
                                Notification::make()
                                    ->title(__('app.notifications.member_deactivated'))
                                    ->danger()
                                    ->send();
                            }))
                            ->visible(fn (Member $record): bool => $record->status?->value === 'active'),
                    ])->dropdown(false),
                    ActionGroup::make([
                        Action::make('heading_actions')
                            ->label(__('app.actions.record_actions'))
                            ->disabled()
                            ->color('gray'),
                        Action::make('send_portal_invitation')
                            ->label(__('app.actions.send_portal_invitation'))
                            ->icon('heroicon-o-envelope')
                            ->requiresConfirmation()
                            ->visible(fn (Member $record): bool => filled($record->email))
                            ->action(function (Member $record): void {
                                app(MemberPortalEmailService::class)->queuePortalInvitation(
                                    $record->id,
                                    auth()->id(),
                                );

                                Notification::make()
                                    ->title(__('app.notifications.portal_invitation_queued'))
                                    ->success()
                                    ->send();
                            }),
                        ResetMemberPasswordAction::make(),
                        Action::make('qr')
                            ->label(__('app.members.qr.title'))
                            ->icon('heroicon-o-qr-code')
                            ->url(fn (Member $record): string => route('web.members.qr', $record))
                            ->openUrlInNewTab(),
                        ViewAction::make(),
                        EditAction::make()->hiddenLabel(),
                        DeleteAction::make()->hiddenLabel(),
                    ])->dropdown(false),
                ]),
            ])->recordUrl(fn ($record): string => route('filament.admin.resources.members.view', $record->id))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
