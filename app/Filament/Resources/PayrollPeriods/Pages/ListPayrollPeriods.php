<?php

namespace App\Filament\Resources\PayrollPeriods\Pages;

use App\Enums\PayrollPeriodStatus;
use App\Filament\Resources\PayrollPeriods\PayrollPeriodResource;
use App\Models\PayrollPeriod;
use App\Services\Hr\PayrollService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayrollPeriods extends ListRecords
{
    protected static string $resource = PayrollPeriodResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label(__('app.hr.actions.generate_payroll'))
                ->icon('heroicon-o-calculator')
                ->schema([
                    Select::make('month')
                        ->label(__('app.hr.fields.month'))
                        ->options(static::monthOptions())
                        ->default((string) now()->month)
                        ->required(),
                    TextInput::make('year')
                        ->label(__('app.hr.fields.year'))
                        ->numeric()
                        ->default((string) now()->year)
                        ->required(),
                ])
                ->action(function (array $data, PayrollService $payrollService): void {
                    $period = $payrollService->generatePeriod(
                        (int) $data['month'],
                        (int) $data['year'],
                        force: true,
                    );

                    Notification::make()
                        ->title(__('app.hr.notifications.payroll_generated'))
                        ->success()
                        ->send();

                    $this->redirect(PayrollPeriodResource::getUrl('view', ['record' => $period]));
                }),
        ];
    }

    #[\Override]
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('app.common.all')),
            'draft' => Tab::make(PayrollPeriodStatus::Draft->getLabel())
                ->badge(PayrollPeriod::query()->where('status', PayrollPeriodStatus::Draft)->count())
                ->badgeColor(PayrollPeriodStatus::Draft->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', PayrollPeriodStatus::Draft)),
            'approved' => Tab::make(PayrollPeriodStatus::Approved->getLabel())
                ->badge(PayrollPeriod::query()->where('status', PayrollPeriodStatus::Approved)->count())
                ->badgeColor(PayrollPeriodStatus::Approved->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', PayrollPeriodStatus::Approved)),
            'paid' => Tab::make(PayrollPeriodStatus::Paid->getLabel())
                ->badge(PayrollPeriod::query()->where('status', PayrollPeriodStatus::Paid)->count())
                ->badgeColor(PayrollPeriodStatus::Paid->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', PayrollPeriodStatus::Paid)),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected static function monthOptions(): array
    {
        $options = [];
        foreach (range(1, 12) as $month) {
            $options[(string) $month] = (string) __('app.hr.months.'.$month);
        }

        return $options;
    }

    #[\Override]
    public function getBreadcrumbs(): array
    {
        return [
            __('app.navigation.groups.hr'),
            PayrollPeriodResource::getNavigationLabel(),
        ];
    }
}
