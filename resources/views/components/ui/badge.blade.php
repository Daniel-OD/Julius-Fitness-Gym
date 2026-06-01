@props([
    'color' => 'gray',
])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-700',
        'brand' => 'bg-brand-50 text-brand-700',
        'green' => 'bg-green-100 text-green-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'red' => 'bg-red-100 text-red-700',
        'blue' => 'bg-blue-100 text-blue-700',
    ];

    $classes = 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium '
        . ($colors[$color] ?? $colors['gray']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
