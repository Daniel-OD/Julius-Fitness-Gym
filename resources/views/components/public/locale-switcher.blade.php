@php
    use App\Support\PublicLocale;

    $currentLocale = app()->getLocale();
    $options = PublicLocale::options();
@endphp

<div x-data="{ open: false }" class="relative">
    <button type="button" @click="open = !open" @keydown.escape.window="open = false"
        class="inline-flex items-center gap-1.5 rounded-full border border-white/15 px-2.5 py-1.5 text-xs font-medium text-white/80 transition-colors hover:bg-white/5"
        :aria-expanded="open" aria-haspopup="listbox" aria-label="{{ __('public.locales.'.$currentLocale) }}">
        <span class="text-base leading-none" aria-hidden="true">{{ PublicLocale::flag($currentLocale) }}</span>
        <svg class="h-3.5 w-3.5 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="m6 9 6 6 6-6" />
        </svg>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
        class="absolute right-0 z-50 mt-2 min-w-[10rem] overflow-hidden rounded-2xl border border-white/10 bg-black/95 py-1 shadow-xl backdrop-blur-xl"
        role="listbox">
        @foreach ($options as $option)
            <a href="{{ route('public.locale', $option['code']) }}"
                @class([
                    'flex items-center gap-2.5 px-3 py-2.5 text-sm transition-colors hover:bg-white/10',
                    'bg-white/5 font-semibold text-white' => $option['code'] === $currentLocale,
                    'text-white/75' => $option['code'] !== $currentLocale,
                ])
                role="option" @if ($option['code'] === $currentLocale) aria-selected="true" @endif>
                <span class="text-base leading-none" aria-hidden="true">{{ $option['flag'] }}</span>
                <span>{{ $option['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
