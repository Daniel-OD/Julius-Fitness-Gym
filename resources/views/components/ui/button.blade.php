@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'loading' => false,
])

@php
    $variants = [
        'primary' =>
            'bg-brand-500 text-white hover:bg-brand-400 jf-glow-accent-hover focus-visible:ring-brand-500/50',
        'secondary' =>
            'border border-zinc-200 bg-white text-zinc-800 hover:border-zinc-300 hover:bg-zinc-50 focus-visible:ring-zinc-300 dark:border-white/12 dark:bg-transparent dark:text-white/90 dark:hover:border-white/20 dark:hover:bg-white/5 dark:focus-visible:ring-white/20',
        'ghost' => 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 focus-visible:ring-zinc-300 dark:text-white/60 dark:hover:bg-white/5 dark:hover:text-white dark:focus-visible:ring-white/20',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus-visible:ring-red-500/50',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-4 py-2 text-sm gap-2',
        'lg' => 'px-6 py-2.5 text-sm gap-2',
    ];

    $base =
        'inline-flex items-center justify-center rounded-full font-semibold tracking-tight transition-all duration-200 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 disabled:cursor-not-allowed disabled:opacity-50 dark:focus-visible:ring-offset-canvas';

    $classes = trim("$base {$variants[$variant]} {$sizes[$size]}");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}
        @if ($loading) disabled aria-busy="true" @endif>
        @if ($loading)
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 0 1 14.93-4.24" />
            </svg>
        @endif
        <span @if ($loading) class="opacity-80" @endif>{{ $slot }}</span>
    </button>
@endif
