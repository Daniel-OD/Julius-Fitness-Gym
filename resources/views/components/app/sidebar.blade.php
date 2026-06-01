{{-- Brand --}}
<div class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 px-6">
    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M6.5 6.5 17.5 17.5" />
            <path d="m21 21-1-1" />
            <path d="m3 3 1 1" />
            <path d="m18 22 4-4" />
            <path d="m2 6 4-4" />
            <path d="m3 10 7-7" />
            <path d="m14 21 7-7" />
        </svg>
    </span>
    <div class="min-w-0">
        <p class="truncate text-sm font-semibold leading-tight text-gray-900">Julius Fitness</p>
        <p class="truncate text-xs text-gray-500">Gym Management</p>
    </div>
    <button type="button" data-sidebar-close
        class="ml-auto rounded-md p-1.5 text-gray-500 hover:bg-gray-100 lg:hidden">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
        </svg>
    </button>
</div>

{{-- Navigation --}}
<nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
    <div class="space-y-1">
        <x-app.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <x-slot:icon>
                <path d="M3 3v18h18" /><path d="m19 9-5 5-4-4-3 3" />
            </x-slot:icon>
            Dashboard
        </x-app.nav-link>
    </div>

    <div>
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
            Membership
        </p>
        <div class="space-y-1">
            <x-app.nav-link href="#" :active="false">
                <x-slot:icon>
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </x-slot:icon>
                Members
            </x-app.nav-link>
            <x-app.nav-link href="#" :active="false">
                <x-slot:icon>
                    <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z" /><path d="M12 7v5l3 3" />
                </x-slot:icon>
                Subscriptions
            </x-app.nav-link>
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
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
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
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
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
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
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

{{-- Footer card --}}
<div class="border-t border-gray-200 p-3">
    <div class="rounded-lg bg-gray-50 p-3 text-xs text-gray-500">
        <p class="font-medium text-gray-700">UI foundation</p>
        <p class="mt-0.5">Placeholder data — connect to backend models once ready.</p>
    </div>
</div>
