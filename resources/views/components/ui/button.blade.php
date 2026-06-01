@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700 focus-visible:ring-brand-500',
        'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus-visible:ring-brand-500',
        'ghost' => 'text-gray-600 hover:bg-gray-100 focus-visible:ring-gray-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs gap-1.5',
        'md' => 'px-3.5 py-2 text-sm gap-2',
        'lg' => 'px-5 py-2.5 text-sm gap-2',
    ];

    $base = 'inline-flex items-center justify-center rounded-lg font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60';

    $classes = trim("$base {$variants[$variant]} {$sizes[$size]}");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
