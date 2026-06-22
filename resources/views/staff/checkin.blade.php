@php
    $tone = match ($status) {
        'success', 'checkout_success' => [
            'icon' => '✅',
            'ring' => 'ring-emerald-500/30',
            'bg' => 'bg-emerald-500/10',
            'text' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'already_checked_in', 'rate_limited' => [
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

@if (! in_array($status, ['already_checked_in'], true))
    @push('head')
        <meta http-equiv="refresh" content="5;url={{ route('home') }}">
    @endpush
@endif

<x-layouts.minimal :title="__('app.hr.checkin.title') . ' · ' . config('app.name')">
    <div class="flex flex-1 flex-col items-center justify-center py-6 text-center sm:py-10">
        <div
            class="flex h-20 w-20 items-center justify-center rounded-full text-3xl ring-8 sm:h-24 sm:w-24 sm:text-4xl {{ $tone['ring'] }} {{ $tone['bg'] }}">
            {{ $tone['icon'] }}
        </div>

        <p class="mt-6 text-lg font-semibold tracking-tight sm:text-xl {{ $tone['text'] }}">
            {{ $message }}
        </p>

        @if ($user)
            <p class="mt-3 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white sm:text-3xl">
                {{ $user->name }}
            </p>
            @if ($profile?->employee_code)
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $profile->employee_code }}
                </p>
            @endif
        @endif

        @if (($canCheckout ?? false) && filled($token ?? null))
            <form method="POST" action="{{ route('staff.checkin.checkout', $token) }}" class="mt-8 w-full max-w-xs">
                @csrf
                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-zinc-900 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200">
                    {{ __('app.hr.checkin.checkout') }}
                </button>
            </form>
        @endif

        @if (! in_array($status, ['already_checked_in'], true))
            <p class="mt-10 text-xs text-zinc-400 dark:text-zinc-500">
                {{ __('app.checkin.redirect_hint') }}
            </p>
        @endif
    </div>
</x-layouts.minimal>
