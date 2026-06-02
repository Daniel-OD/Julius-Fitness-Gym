@props([
    'color' => 'gray',
])

@php
    $colors = [
        'gray' => 'border border-white/10 bg-white/8 text-white/70',
        'brand' => 'border border-brand-500/30 bg-brand-500/15 text-brand-300',
        'green' => 'border border-emerald-500/25 bg-emerald-500/12 text-emerald-300',
        'amber' => 'border border-amber-500/25 bg-amber-500/12 text-amber-300',
        'red' => 'border border-red-500/25 bg-red-500/12 text-red-300',
        'blue' => 'border border-blue-500/25 bg-blue-500/12 text-blue-300',
    ];

    $classes = 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium '
        . ($colors[$color] ?? $colors['gray']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
