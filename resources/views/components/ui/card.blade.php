@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'jf-surface overflow-hidden rounded-2xl']) }}>
    @if ($title || isset($actions))
        <div
            class="flex items-center justify-between gap-4 border-b border-zinc-200 px-5 py-4 dark:border-white/8">
            <div class="min-w-0">
                @if ($title)
                    <h3 class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="mt-0.5 truncate text-xs text-zinc-500 dark:text-white/45">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $padding ? 'p-5' : '' }}">
        {{ $slot }}
    </div>
</div>
