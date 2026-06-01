@props([
    'href' => '#',
    'active' => false,
])

@php
    $base = 'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors';
    $state = $active
        ? 'bg-brand-50 text-brand-700'
        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';
    $iconState = $active
        ? 'text-brand-600'
        : 'text-gray-400 group-hover:text-gray-600';
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
