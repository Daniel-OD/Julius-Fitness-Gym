<?php

namespace App\Filament\Office\Pages;

use App\Enums\PayrollItemStatus;
use App\Enums\PayrollPeriodStatus;
use App\Models\PayrollItem;
use App\Support\Hr\PayslipPdfRenderer;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyPayslips extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $panel = 'office';

    protected static ?string $slug = 'my-payslips';

    protected static ?int $navigationSort = 22;

    protected string $view = 'filament.office.pages.my-payslips';

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return __('app.hr.office.my_payslips');
    }

    #[\Override]
    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasStaffProfile() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => PayrollItem::query()
                ->where('user_id', auth()->id())
                ->whereIn('status', [PayrollItemStatus::Approved, PayrollItemStatus::Paid])
                ->whereHas('period', fn (Builder $query): Builder => $query->whereIn('status', [
                    PayrollPeriodStatus::Approved,
                    PayrollPeriodStatus::Paid,
                ])))
            ->columns([
                TextColumn::make('period.label')
                    ->label(__('app.hr.fields.period'))
                    ->state(fn (PayrollItem $record): string => $record->period?->label() ?? '—'),
                TextColumn::make('gross')
                    ->label(__('app.hr.fields.gross'))
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('net')
                    ->label(__('app.hr.fields.net'))
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('status')
                    ->label(__('app.fields.status'))
                    ->badge(),
            ])
            ->recordActions([
                Action::make('download')
                    ->label(__('app.hr.actions.download_payslip'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (PayrollItem $record): StreamedResponse => $this->downloadPayslip($record)),
            ])
            ->defaultSort('period_id', 'desc')
            ->paginated([12, 24]);
    }

    protected function downloadPayslip(PayrollItem $record): StreamedResponse
    {
        abort_unless($record->user_id === auth()->id(), 403);

        $pdf = app(PayslipPdfRenderer::class)->render($record);
        $period = $record->period;
        $filename = sprintf('payslip-%02d-%d.pdf', $period?->month ?? 0, $period?->year ?? 0);

        return response()->streamDownload(fn () => print ($pdf), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
