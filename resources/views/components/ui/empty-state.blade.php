@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <div
        class="mb-4 flex h-12 w-12 items-center justify-center rounded-full border border-zinc-200 bg-zinc-50 text-zinc-400 dark:border-white/10 dark:bg-white/5 dark:text-white/35">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 7h16M4 12h16M4 17h10" />
        </svg>
    </div>
    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-white/45">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-5">{{ $action }}</div>
    @endif
</div>
