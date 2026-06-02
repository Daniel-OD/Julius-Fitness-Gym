<header
    class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-white/8 bg-canvas/80 px-4 backdrop-blur-xl sm:px-6 lg:px-8">
    <button type="button" data-sidebar-toggle
        class="rounded-full p-2 text-white/50 transition-colors hover:bg-white/5 lg:hidden">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 12h16" /><path d="M4 6h16" /><path d="M4 18h16" />
        </svg>
    </button>

    <div class="relative hidden max-w-xs flex-1 sm:block">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/30" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" />
        </svg>
        <input type="search" placeholder="Caută membri, facturi…" class="jf-input py-2 pl-9">
    </div>

    <div class="ml-auto flex items-center gap-1 sm:gap-2">
        <button type="button" title="Notificări"
            class="relative rounded-full p-2 text-white/50 transition-colors hover:bg-white/5">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.268 21a2 2 0 0 0 3.464 0" />
                <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326" />
            </svg>
            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-brand-500 ring-2 ring-canvas"></span>
        </button>

        <div class="relative" data-dropdown>
            <button type="button" data-dropdown-trigger
                class="flex items-center gap-2 rounded-full p-1 pr-2 transition-colors hover:bg-white/5">
                <span
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </span>
                <span class="hidden text-left sm:block">
                    <span class="block text-sm font-medium leading-tight text-white">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </span>
                    <span class="block text-xs leading-tight text-white/40">Administrator</span>
                </span>
                <svg class="h-4 w-4 text-white/35" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </button>

            <div data-dropdown-menu
                class="absolute right-0 mt-2 hidden w-48 overflow-hidden rounded-xl border border-white/10 bg-surface-elevated py-1 shadow-2xl">
                <a href="#"
                    class="block px-4 py-2 text-sm text-white/70 transition-colors hover:bg-white/5 hover:text-white">
                    Profil
                </a>
                <a href="#"
                    class="block px-4 py-2 text-sm text-white/70 transition-colors hover:bg-white/5 hover:text-white">
                    Setări
                </a>
                <div class="my-1 border-t border-white/8"></div>
                @if (Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="block w-full px-4 py-2 text-left text-sm text-red-400 transition-colors hover:bg-white/5">
                            Deconectare
                        </button>
                    </form>
                @else
                    <a href="#"
                        class="block px-4 py-2 text-sm text-red-400 transition-colors hover:bg-white/5">
                        Deconectare
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>
