{{--
    Theme switcher button — Light / Dark toggle.
    Uses Filament's Alpine $store('theme') so the preference persists
    in localStorage without any server request.
--}}
<div class="fi-topbar-item"
     x-data
     x-cloak>
    <button
        type="button"
        x-on:click="
            const next = $store('theme').theme === 'dark' ? 'light' : 'dark';
            $store('theme').theme = next;
        "
        class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-sm font-medium shadow-sm ring-1 ring-black/5 backdrop-blur transition-all duration-150 hover:scale-105 hover:bg-white dark:bg-gray-800/80 dark:ring-white/10 dark:hover:bg-gray-800"
        title="{{ __('app.ui.toggle_theme') }}"
        aria-label="{{ __('app.ui.toggle_theme') }}"
    >
        {{-- Sun icon (shown in dark mode → click goes light) --}}
        <svg x-show="$store('theme').theme === 'dark'"
             class="h-4 w-4 text-amber-400"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.75" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
        </svg>
        {{-- Moon icon (shown in light mode → click goes dark) --}}
        <svg x-show="$store('theme').theme !== 'dark'"
             class="h-4 w-4 text-gray-600"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.75" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
        </svg>
    </button>
</div>
