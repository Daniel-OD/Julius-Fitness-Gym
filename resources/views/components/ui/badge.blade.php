@props([
    'color' => 'gray',
])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-700/40 dark:text-gray-300',
        'brand' => 'bg-brand-50 text-brand-700 dark:bg-brand-600/15 dark:text-brand-300',
        'green' => 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400',
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
        'red' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400',
        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
    ];

    $classes = 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium '
        . ($colors[$color] ?? $colors['gray']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
