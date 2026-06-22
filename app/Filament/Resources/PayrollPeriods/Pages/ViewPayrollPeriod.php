<?php

namespace App\Filament\Resources\PayrollPeriods\Pages;

use App\Enums\PayrollPeriodStatus;
use App\Filament\Resources\PayrollPeriods\PayrollPeriodResource;
use App\Models\PayrollPeriod;
use App\Services\Hr\PayrollService;
use App\Support\Hr\PayrollPeriodPdfRenderer;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property-read PayrollPeriod $record
 */
class ViewPayrollPeriod extends ViewRecord
{
    protected static string $resource = PayrollPeriodResource::class;

    #[\Override]
    public function getTitle(): string
    {
        return __('app.hr.titles.payroll_period', ['period' => $this->record->label()]);
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label(__('app.hr.actions.regenerate'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status !== PayrollPeriodStatus::Approved)
                ->action(function (PayrollService $payrollService): void {
                    $payrollService->generatePeriod(
                        $this->record->month,
                        $this->record->year,
                        force: true,
                    );

                    Notification::make()
                        ->title(__('app.hr.notifications.payroll_generated'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'generated_at']);
                }),
            Action::make('approve')
                ->label(__('app.hr.actions.approve_period'))
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === PayrollPeriodStatus::Draft)
                ->action(function (PayrollService $payrollService): void {
                    $payrollService->approvePeriod($this->record, auth()->user());

                    Notification::make()
                        ->title(__('app.hr.notifications.payroll_approved'))
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'approved_by']);
                }),
            Action::make('exportPdf')
                ->label(__('app.hr.actions.export_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn (): bool => in_array($this->record->status, [PayrollPeriodStatus::Approved, PayrollPeriodStatus::Paid]))
                ->action(fn (PayrollPeriodPdfRenderer $renderer): StreamedResponse => $this->exportPdf($renderer)),
            Action::make('exportCsv')
                ->label(__('app.hr.actions.export_csv'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (): StreamedResponse => $this->exportCsv()),
        ];
    }

    protected function exportPdf(PayrollPeriodPdfRenderer $renderer): StreamedResponse
    {
        $filename = sprintf(
            'payroll-%d-%02d-%s.pdf',
            $this->record->year,
            $this->record->month,
            ($this->record->generated_at ?? now())->format('Ymd-His'),
        );

        $pdf = $renderer->render($this->record);

        return response()->streamDownload(fn () => print ($pdf), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    protected function exportCsv(): StreamedResponse
    {
        $period = $this->record->loadMissing(['items.user']);
        $filename = sprintf('payroll-%02d-%d.csv', $period->month, $period->year);

        return response()->streamDownload(function () use ($period): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                __('app.hr.fields.user'),
                __('app.hr.fields.base_salary'),
                __('app.hr.fields.working_days'),
                __('app.hr.fields.present_days'),
                __('app.hr.fields.overtime_hours'),
                __('app.hr.fields.gross'),
                __('app.hr.fields.net'),
                __('app.fields.status'),
            ], escape: '\\');

            foreach ($period->items as $item) {
                fputcsv($handle, [
                    $item->user->name ?? '—',
                    $item->base_salary,
                    $item->working_days,
                    $item->present_days,
                    $item->overtime_hours,
                    $item->gross,
                    $item->net,
                    $item->status->getLabel(),
                ], escape: '\\');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.hr'),
            PayrollPeriodResource::getUrl('index') => PayrollPeriodResource::getNavigationLabel(),
            $this->record->label(),
        ];
    }
}
