@props([
    'href' => '#',
    'active' => false,
])

@php
    $base = 'group flex items-center gap-3 rounded-full px-3 py-2 text-sm font-medium transition-all duration-200';
    $state = $active
        ? 'bg-brand-500/15 text-brand-600 dark:text-brand-300 jf-glow-accent'
        : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-white/55 dark:hover:bg-white/5 dark:hover:text-white';
    $iconState = $active
        ? 'text-brand-500 dark:text-brand-400'
        : 'text-zinc-400 group-hover:text-zinc-600 dark:text-white/35 dark:group-hover:text-white/60';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "$base $state"]) }}>
    @isset($icon)
        <svg class="h-5 w-5 shrink-0 {{ $iconState }}" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            {{ $icon }}
        </svg>
    @endisset
    <span class="truncate">{{ $slot }}</span>
</a>
