<div class="flex h-16 shrink-0 items-center gap-3 border-b border-zinc-200 px-6 dark:border-white/8">
    <x-application-mark class="h-9 w-9 shrink-0" />
    <div class="min-w-0">
        <p class="truncate text-sm font-semibold leading-tight text-zinc-900 dark:text-white">Julius Fitness</p>
        <p class="truncate text-xs text-zinc-500 dark:text-white/40">Gym Management</p>
    </div>
    <button type="button" data-sidebar-close
        class="ml-auto rounded-full p-1.5 text-zinc-500 transition-colors hover:bg-zinc-100 dark:text-white/50 dark:hover:bg-white/5 lg:hidden">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
        </svg>
    </button>
</div>

<nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
    <div class="space-y-1">
        <x-app.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <x-slot:icon>
                <path d="M3 3v18h18" /><path d="m19 9-5 5-4-4-3 3" />
            </x-slot:icon>
            Dashboard
        </x-app.nav-link>
    </div>

    <div>
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-white/30">
            Membership
        </p>
        <div class="space-y-1">
            <x-app.nav-link href="#" :active="false">
                <x-slot:icon>
                    <path d="m12 8-9.04 9.06a2.82 2.82 0 1 0 3.98 3.98L16 12" /><circle cx="17" cy="7" r="5" />
                </x-slot:icon>
                Plans
            </x-app.nav-link>
            <x-app.nav-link href="#" :active="false">
                <x-slot:icon>
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76Z" />
                </x-slot:icon>
                Services
            </x-app.nav-link>
        </div>
    </div>

    <div>
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-white/30">
            Finance
        </p>
        <div class="space-y-1">
            <x-app.nav-link href="#" :active="false">
                <x-slot:icon>
                    <path d="M14 2v6h6" /><path d="M4 22V4a2 2 0 0 1 2-2h8l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z" />
                    <path d="M8 13h8" /><path d="M8 17h6" />
                </x-slot:icon>
                Invoices
            </x-app.nav-link>
            <x-app.nav-link href="#" :active="false">
                <x-slot:icon>
                    <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z" />
                    <path d="M13 5v2" /><path d="M13 17v2" /><path d="M13 11v2" />
                </x-slot:icon>
                Expenses
            </x-app.nav-link>
        </div>
    </div>

    <div>
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-white/30">
            CRM
        </p>
        <div class="space-y-1">
            <x-app.nav-link href="#" :active="false">
                <x-slot:icon>
                    <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
                </x-slot:icon>
                Enquiries
            </x-app.nav-link>
        </div>
    </div>

    <div>
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-white/30">
            Admin
        </p>
        <div class="space-y-1">
            <x-app.nav-link href="#" :active="false">
                <x-slot:icon>
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />
                    <path d="M19 8v6" /><path d="M22 11h-6" />
                </x-slot:icon>
                Users
            </x-app.nav-link>
            <x-app.nav-link href="#" :active="false">
                <x-slot:icon>
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z" />
                    <circle cx="12" cy="12" r="3" />
                </x-slot:icon>
                Settings
            </x-app.nav-link>
        </div>
    </div>
</nav>

<div class="border-t border-zinc-200 p-3 dark:border-white/8">
    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs text-zinc-500 dark:border-white/8 dark:bg-surface dark:text-white/45">
        <p class="font-medium text-zinc-700 dark:text-white/70">Julius Fitness</p>
        <p class="mt-1 leading-relaxed">SpaceX-inspired dark UI</p>
    </div>
</div>
