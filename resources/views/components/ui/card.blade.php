@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<div
    {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white shadow-sm']) }}>
    @if ($title || isset($actions))
        <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4">
            <div class="min-w-0">
                @if ($title)
                    <h3 class="truncate text-sm font-semibold text-gray-900">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="mt-0.5 truncate text-xs text-gray-500">{{ $subtitle }}</p>
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
