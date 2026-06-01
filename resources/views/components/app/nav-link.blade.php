@props([
    'href' => '#',
    'active' => false,
])

@php
    $base = 'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors';
    $state = $active
        ? 'bg-brand-50 text-brand-700 dark:bg-brand-600/10 dark:text-brand-300'
        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white';
    $iconState = $active
        ? 'text-brand-600 dark:text-brand-400'
        : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300';
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
