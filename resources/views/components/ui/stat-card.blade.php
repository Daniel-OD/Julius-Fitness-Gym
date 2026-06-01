@props([
    'label' => '',
    'value' => '',
    'trend' => null,
    'trendUp' => true,
    'color' => 'brand',
])

@php
    $iconColors = [
        'brand' => 'bg-brand-50 text-brand-600',
        'green' => 'bg-green-50 text-green-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'amber' => 'bg-amber-50 text-amber-600',
    ];
@endphp

<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900">{{ $value }}</p>
        </div>
        @isset($icon)
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $iconColors[$color] ?? $iconColors['brand'] }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    {{ $icon }}
                </svg>
            </span>
        @endisset
    </div>

    @if ($trend)
        <div class="mt-3 flex items-center gap-1 text-xs">
            <span class="inline-flex items-center gap-0.5 font-medium {{ $trendUp ? 'text-green-600' : 'text-red-600' }}">
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
            <span class="text-gray-400">vs last month</span>
        </div>
    @endif
</div>
