@props([
    'label' => '',
    'value' => '',
    'trend' => null,
    'trendUp' => true,
    'color' => 'brand',
])

@php
    $iconColors = [
        'brand' => 'border border-brand-500/25 bg-brand-500/12 text-brand-400',
        'green' => 'border border-emerald-500/25 bg-emerald-500/12 text-emerald-400',
        'blue' => 'border border-blue-500/25 bg-blue-500/12 text-blue-400',
        'amber' => 'border border-amber-500/25 bg-amber-500/12 text-amber-400',
    ];
@endphp

<div class="jf-surface rounded-2xl p-5">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-white/45">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold tracking-tight text-white">{{ $value }}</p>
        </div>
        @isset($icon)
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $iconColors[$color] ?? $iconColors['brand'] }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    {{ $icon }}
                </svg>
            </span>
        @endisset
    </div>

    @if ($trend)
        <div class="mt-3 flex items-center gap-1 text-xs">
            <span class="inline-flex items-center gap-0.5 font-medium {{ $trendUp ? 'text-emerald-400' : 'text-red-400' }}">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    @if ($trendUp)
                        <path d="M7 17 17 7" /><path d="M7 7h10v10" />
                    @else
                        <path d="M7 7 17 17" /><path d="M17 7v10H7" />
                    @endif
                </svg>
                {{ $trend }}
            </span>
            <span class="text-white/30">vs last month</span>
        </div>
    @endif
</div>
