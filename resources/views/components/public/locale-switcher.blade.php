@php
    use App\Support\PublicLocale;

    $currentLocale = app()->getLocale();
    $options = PublicLocale::options();
@endphp

<div x-data="{ open: false }" class="relative">
    <button type="button" @click="open = !open" @keydown.escape.window="open = false"
        class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 px-2.5 py-1.5 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-100 dark:border-white/15 dark:text-white/80 dark:hover:bg-white/5"
        :aria-expanded="open" aria-haspopup="listbox" aria-label="{{ __('public.locales.'.$currentLocale) }}">
        <span class="text-base leading-none" aria-hidden="true">{{ PublicLocale::flag($currentLocale) }}</span>
        <svg class="h-3.5 w-3.5 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="m6 9 6 6 6-6" />
        </svg>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
        class="absolute right-0 z-50 mt-2 min-w-[10rem] overflow-hidden rounded-2xl border border-zinc-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-black/95 dark:shadow-none dark:backdrop-blur-xl"
        role="listbox">
        @foreach ($options as $option)
            <a href="{{ route('public.locale', $option['code']) }}"
                @class([
                    'flex items-center gap-2.5 px-3 py-2.5 text-sm transition-colors hover:bg-zinc-50 dark:hover:bg-white/10',
                    'bg-zinc-100 font-semibold text-zinc-900 dark:bg-white/5 dark:text-white' => $option['code'] === $currentLocale,
                    'text-zinc-600 dark:text-white/75' => $option['code'] !== $currentLocale,
                ])
                role="option" @if ($option['code'] === $currentLocale) aria-selected="true" @endif>
                <span class="text-base leading-none" aria-hidden="true">{{ $option['flag'] }}</span>
                <span>{{ $option['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
