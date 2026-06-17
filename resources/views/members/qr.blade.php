@php
    $statusClasses = match ($access->tone) {
        'active' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
        'expired' => 'bg-orange-500/10 text-orange-700 dark:text-orange-400',
        default => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400',
    };
@endphp

<x-layouts.minimal :title="$member->name . ' · QR'">
    <div class="flex flex-1 flex-col items-center text-center">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $member->name }}</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $member->code }}</p>

        <div
            class="mt-10 w-full max-w-xs rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-zinc-950">
            <div class="mx-auto aspect-square w-full max-w-[240px] [&_svg]:h-full [&_svg]:w-full">
                {!! $qrSvg !!}
            </div>
        </div>

        <p class="mt-8 inline-flex items-center rounded-full px-4 py-2 text-sm font-medium {{ $statusClasses }}">
            @if ($access->isActive)
                <span class="mr-2 h-2 w-2 rounded-full bg-emerald-500"></span>
            @elseif ($access->tone === 'expired')
                <span class="mr-2 h-2 w-2 rounded-full bg-orange-500"></span>
            @endif
            {{ $access->label }}
        </p>

        <div class="mt-10 flex w-full max-w-xs flex-col gap-3 sm:flex-row sm:justify-center">
            <button type="button" onclick="window.print()"
                class="inline-flex flex-1 items-center justify-center gap-2 rounded-full border border-zinc-200 bg-white px-5 py-3 text-sm font-semibold text-zinc-900 transition-colors hover:bg-zinc-50 dark:border-white/15 dark:bg-zinc-900 dark:text-white dark:hover:bg-zinc-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z" />
                </svg>
                {{ __('app.members.qr.print') }}
            </button>
            <a href="{{ route('web.members.qr.download', $member) }}"
                class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-zinc-900 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5m0 0 5-5m-5 5V4" />
                </svg>
                {{ __('app.members.qr.download') }}
            </a>
        </div>

        <a href="{{ route('filament.admin.resources.members.view', $member) }}"
            class="mt-8 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
            ← {{ __('app.members.qr.back') }}
        </a>
    </div>

    <style>
        @media print {
            [data-theme-toggle],
            a[href*='members'],
            .mt-10.flex {
                display: none !important;
            }

            body {
                background: #fff !important;
                color: #000 !important;
            }

            .dark body {
                background: #fff !important;
            }
        }
    </style>
</x-layouts.minimal>
