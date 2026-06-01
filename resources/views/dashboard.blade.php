@php
    // Placeholder data — replace with real metrics from the backend (AnalyticsService) once wired.
    $recentMembers = [
        ['name' => 'Andrei Popescu', 'plan' => 'Premium Annual', 'status' => 'active', 'joined' => '2 hours ago'],
        ['name' => 'Maria Ionescu', 'plan' => 'Monthly Standard', 'status' => 'active', 'joined' => '5 hours ago'],
        ['name' => 'Vlad Georgescu', 'plan' => 'Quarterly Plus', 'status' => 'pending', 'joined' => 'Yesterday'],
        ['name' => 'Elena Dumitrescu', 'plan' => 'Premium Annual', 'status' => 'active', 'joined' => 'Yesterday'],
        ['name' => 'Cristian Marin', 'plan' => 'Day Pass', 'status' => 'expired', 'joined' => '2 days ago'],
    ];

    $recentInvoices = [
        ['number' => 'INV-001042', 'member' => 'Andrei Popescu', 'amount' => '1,200 lei', 'status' => 'paid'],
        ['number' => 'INV-001041', 'member' => 'Maria Ionescu', 'amount' => '150 lei', 'status' => 'paid'],
        ['number' => 'INV-001040', 'member' => 'Vlad Georgescu', 'amount' => '450 lei', 'status' => 'pending'],
        ['number' => 'INV-001039', 'member' => 'Cristian Marin', 'amount' => '50 lei', 'status' => 'overdue'],
    ];

    $statusColors = [
        'active' => 'green',
        'paid' => 'green',
        'pending' => 'amber',
        'expired' => 'gray',
        'overdue' => 'red',
    ];
@endphp

<x-layouts.app title="Dashboard">
    <x-slot:header>
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Welcome back — here's what's happening at the gym.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button variant="secondary" size="md">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M7 10l5 5 5-5" /><path d="M12 15V3" />
                </svg>
                Export
            </x-ui.button>
            <x-ui.button variant="primary" size="md">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" /><path d="M12 5v14" />
                </svg>
                New member
            </x-ui.button>
        </div>
    </x-slot:header>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Active members" value="342" trend="12%" :trendUp="true" color="brand">
            <x-slot:icon>
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </x-slot:icon>
        </x-ui.stat-card>

        <x-ui.stat-card label="Monthly revenue" value="48,250 lei" trend="8%" :trendUp="true" color="green">
            <x-slot:icon>
                <path d="M12 2v20" /><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </x-slot:icon>
        </x-ui.stat-card>

        <x-ui.stat-card label="Active subscriptions" value="389" trend="3%" :trendUp="true" color="blue">
            <x-slot:icon>
                <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z" /><path d="M12 7v5l3 3" />
            </x-slot:icon>
        </x-ui.stat-card>

        <x-ui.stat-card label="Open enquiries" value="17" trend="5%" :trendUp="false" color="amber">
            <x-slot:icon>
                <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" />
            </x-slot:icon>
        </x-ui.stat-card>
    </div>

    {{-- Main grid --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Recent members --}}
        <x-ui.card title="Recent members" subtitle="Latest sign-ups" :padding="false" class="lg:col-span-2">
            <x-slot:actions>
                <x-ui.button variant="ghost" size="sm" href="#">View all</x-ui.button>
            </x-slot:actions>

            <x-ui.table :headings="['Member', 'Plan', 'Status', 'Joined']">
                @foreach ($recentMembers as $member)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="whitespace-nowrap px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ strtoupper(substr($member['name'], 0, 1)) }}
                                </span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $member['name'] }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-600 dark:text-gray-400">{{ $member['plan'] }}</td>
                        <td class="whitespace-nowrap px-5 py-3">
                            <x-ui.badge :color="$statusColors[$member['status']] ?? 'gray'">
                                {{ ucfirst($member['status']) }}
                            </x-ui.badge>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400">{{ $member['joined'] }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>

        {{-- Recent invoices --}}
        <x-ui.card title="Recent invoices" subtitle="Last transactions" :padding="false">
            <x-slot:actions>
                <x-ui.button variant="ghost" size="sm" href="#">View all</x-ui.button>
            </x-slot:actions>

            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($recentInvoices as $invoice)
                    <li class="flex items-center justify-between gap-3 px-5 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $invoice['number'] }}</p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $invoice['member'] }}</p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $invoice['amount'] }}</span>
                            <x-ui.badge :color="$statusColors[$invoice['status']] ?? 'gray'">
                                {{ ucfirst($invoice['status']) }}
                            </x-ui.badge>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    </div>
</x-layouts.app>
