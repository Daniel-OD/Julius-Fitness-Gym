@php
    $variant = $attributes->get('variant', 'footer');
@endphp

<p {{ $attributes->merge(['class' => match ($variant) {
    'inline' => 'text-[10px] text-zinc-400 dark:text-white/25',
    'login' => 'mt-6 text-center text-[10px] text-zinc-400 dark:text-white/30',
    default => 'text-[10px] text-zinc-400 dark:text-white/25',
}]) }}>
    <span class="sr-only">{{ __('app.studio.built_by') }}</span>
    <a href="{{ config('studio.repository') }}" target="_blank" rel="noopener noreferrer"
        class="transition-colors hover:text-brand-500 dark:hover:text-brand-400"
        title="{{ config('studio.signature') }}">
        {{ config('studio.signature') }}
    </a>
</p>
