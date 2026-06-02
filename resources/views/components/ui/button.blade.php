@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' =>
            'bg-brand-500 text-white hover:bg-brand-400 jf-glow-accent-hover focus-visible:ring-brand-500/50',
        'secondary' =>
            'border border-white/12 bg-transparent text-white/90 hover:border-white/20 hover:bg-white/5 focus-visible:ring-white/20',
        'ghost' => 'text-white/60 hover:bg-white/5 hover:text-white focus-visible:ring-white/20',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus-visible:ring-red-500/50',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-4 py-2 text-sm gap-2',
        'lg' => 'px-6 py-2.5 text-sm gap-2',
    ];

    $base =
        'inline-flex items-center justify-center rounded-full font-semibold tracking-tight transition-all duration-200 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-canvas disabled:cursor-not-allowed disabled:opacity-50';

    $classes = trim("$base {$variants[$variant]} {$sizes[$size]}");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
