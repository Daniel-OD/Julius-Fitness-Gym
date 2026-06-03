@php
    $tone = match ($status) {
        'success' => [
            'icon' => '✅',
            'ring' => 'ring-emerald-500/30',
            'bg' => 'bg-emerald-500/10',
            'text' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'warning', 'rate_limited' => [
            'icon' => '⚠️',
            'ring' => 'ring-orange-500/30',
            'bg' => 'bg-orange-500/10',
            'text' => 'text-orange-600 dark:text-orange-400',
        ],
        default => [
            'icon' => '❌',
            'ring' => 'ring-red-500/30',
            'bg' => 'bg-red-500/10',
            'text' => 'text-red-600 dark:text-red-400',
        ],
    };
@endphp

@push('head')
    <meta http-equiv="refresh" content="5;url={{ route('home') }}">
@endpush

<x-layouts.minimal :title="__('app.checkin.title') . ' · ' . config('app.name')">
    <div class="flex flex-1 flex-col items-center justify-center py-6 text-center sm:py-10">
        <div
            class="flex h-20 w-20 items-center justify-center rounded-full text-3xl ring-8 sm:h-24 sm:w-24 sm:text-4xl {{ $tone['ring'] }} {{ $tone['bg'] }}">
            {{ $tone['icon'] }}
        </div>

        <p class="mt-6 text-lg font-semibold tracking-tight sm:text-xl {{ $tone['text'] }}">
            {{ $message }}
        </p>

        @if ($member)
            <p class="mt-3 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white sm:text-3xl">
                {{ $member->name }}
            </p>
        @endif

        @if ($checkIn?->checked_in_at)
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('app.checkin.checked_in_at', [
                    'time' => $checkIn->checked_in_at->timezone(config('app.timezone'))->format('H:i'),
                ]) }}
            </p>
        @endif

        @if ($member && ($subscription || $checkIn))
            <dl
                class="mt-8 w-full max-w-sm rounded-2xl border border-zinc-200 bg-white p-4 text-left text-sm dark:border-white/10 dark:bg-zinc-950">
                @if ($subscription?->plan)
                    <div class="mb-3">
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            {{ __('app.resources.plans.singular') }}
                        </dt>
                        <dd class="mt-0.5 font-semibold text-zinc-900 dark:text-white">{{ $subscription->plan->name }}</dd>
                    </div>
                @endif
                @if ($subscription?->end_date)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            {{ __('app.fields.end_date') }}
                        </dt>
                        <dd class="mt-0.5 font-semibold text-zinc-900 dark:text-white">
                            {{ $subscription->end_date->translatedFormat('d M Y') }}
                        </dd>
                    </div>
                @endif
            </dl>
        @endif

        <p class="mt-10 text-xs text-zinc-400 dark:text-zinc-500">
            {{ __('app.checkin.redirect_hint') }}
        </p>
    </div>
</x-layouts.minimal>
