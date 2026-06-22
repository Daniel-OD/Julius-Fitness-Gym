<x-filament-panels::page>
    <div class="mb-4 flex items-center justify-between gap-4">
        <x-filament::button color="gray" wire:click="previousMonth">
            {{ __('app.hr.calendar.previous') }}
        </x-filament::button>
        <h2 class="text-lg font-semibold">{{ $this->monthLabel() }}</h2>
        <x-filament::button color="gray" wire:click="nextMonth">
            {{ __('app.hr.calendar.next') }}
        </x-filament::button>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
        <div class="grid grid-cols-7 bg-gray-50 text-xs font-medium uppercase tracking-wide text-gray-500 dark:bg-white/5 dark:text-gray-400">
            @foreach (range(0, 6) as $day)
                <div class="border-b border-r border-gray-200 px-2 py-2 last:border-r-0 dark:border-white/10">
                    {{ __('app.hr.days_of_week.'.$day) }}
                </div>
            @endforeach
        </div>

        @foreach ($this->calendarWeeks() as $week)
            <div class="grid grid-cols-7">
                @foreach ($week as $cell)
                    <div @class([
                        'min-h-24 border-b border-r border-gray-200 p-2 last:border-r-0 dark:border-white/10',
                        'bg-white dark:bg-gray-950' => $cell['in_month'],
                        'bg-gray-50/50 dark:bg-white/[0.02]' => ! $cell['in_month'],
                    ])>
                        @if ($cell['day'])
                            <div class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $cell['day'] }}</div>
                            <div class="space-y-1">
                                @foreach ($cell['leaves'] as $leave)
                                    <div class="truncate rounded bg-primary-500/10 px-1.5 py-0.5 text-xs text-primary-700 dark:text-primary-300">
                                        {{ $leave->user?->name }} · {{ $leave->type->getLabel() }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
