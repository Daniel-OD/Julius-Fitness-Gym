<?php

namespace App\Filament\Office\Pages;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Leave;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

/**
 * @property-read Schema $form
 */
class RequestLeave extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $panel = 'office';

    protected static ?string $slug = 'request-leave';

    protected static ?int $navigationSort = 21;

    protected string $view = 'filament.office.pages.request-leave';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    #[\Override]
    public static function getNavigationLabel(): string
    {
        return __('app.hr.office.request_leave');
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

    public function mount(): void
    {
        $this->form->fill([
            'type' => LeaveType::Annual->value,
            'days' => 1,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make()->schema([
                    Select::make('type')
                        ->label(__('app.fields.type'))
                        ->options(LeaveType::class)
                        ->required(),
                    DatePicker::make('start_date')
                        ->label(__('app.hr.fields.start_date'))
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn () => $this->syncDays()),
                    DatePicker::make('end_date')
                        ->label(__('app.hr.fields.end_date'))
                        ->required()
                        ->afterOrEqual('start_date')
                        ->live()
                        ->afterStateUpdated(fn () => $this->syncDays()),
                    TextInput::make('days')
                        ->label(__('app.hr.fields.days'))
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Textarea::make('reason')
                        ->label(__('app.hr.fields.reason'))
                        ->rows(3),
                ]),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        Leave::create([
            'user_id' => auth()->id(),
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days' => (int) $data['days'],
            'status' => LeaveStatus::Pending,
            'reason' => $data['reason'] ?? null,
        ]);

        Notification::make()
            ->title(__('app.hr.notifications.leave_submitted'))
            ->success()
            ->send();

        $this->form->fill([
            'type' => LeaveType::Annual->value,
            'days' => 1,
            'start_date' => null,
            'end_date' => null,
            'reason' => null,
        ]);
    }

    protected function syncDays(): void
    {
        $start = $this->data['start_date'] ?? null;
        $end = $this->data['end_date'] ?? null;

        if (blank($start) || blank($end)) {
            return;
        }

        $this->data['days'] = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
    }
}
