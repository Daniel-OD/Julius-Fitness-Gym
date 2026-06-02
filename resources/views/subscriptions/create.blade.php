@php
    // Placeholder — controller: $member, $plans, $paymentMethods
    $member = [
        'id' => 1,
        'name' => 'Ana Popescu',
        'code' => 'MEM-001',
        'initials' => 'AP',
    ];

    $plans = [
        ['id' => 1, 'name' => 'Abonament 1 lună', 'price' => '150 RON', 'duration' => '30 zile'],
        ['id' => 2, 'name' => 'Abonament 3 luni', 'price' => '400 RON', 'duration' => '90 zile'],
        ['id' => 3, 'name' => 'Abonament 12 luni', 'price' => '1.200 RON', 'duration' => '365 zile'],
        ['id' => 4, 'name' => 'Day Pass', 'price' => '50 RON', 'duration' => '1 zi'],
        ['id' => 5, 'name' => 'Premium + PT', 'price' => '350 RON', 'duration' => '30 zile'],
    ];

    $paymentMethods = [
        'cash' => 'Numerar',
        'card' => 'Card',
        'transfer' => 'Transfer bancar',
        'online' => 'Plată online',
    ];
@endphp

<x-layouts.app :title="'Abonament nou · ' . config('app.name')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-gray-500">
                    <a href="#" class="hover:text-brand-600">Membri</a>
                    <span>/</span>
                    <a href="#" class="hover:text-brand-600">{{ $member['name'] }}</a>
                    <span>/</span>
                    <span class="text-gray-900">Abonament nou</span>
                </nav>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Abonament nou</h1>
                <p class="mt-1 text-sm text-gray-500">Asociază un plan de membership acestui membru.</p>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-2xl space-y-6">
        {{-- Membru selectat --}}
        <div class="flex items-center gap-4 rounded-xl border border-brand-100 bg-brand-50/60 px-5 py-4">
            <span
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 text-sm font-semibold text-white">
                {{ $member['initials'] }}
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium uppercase tracking-wider text-brand-700">Membru</p>
                <p class="truncate font-semibold text-gray-900">{{ $member['name'] }}</p>
                <p class="text-sm text-gray-600">{{ $member['code'] }}</p>
            </div>
            <a href="#" class="shrink-0 text-sm font-medium text-brand-600 hover:text-brand-700">Schimbă</a>
        </div>

        <form action="#" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="member_id" value="{{ $member['id'] }}" />

            <x-ui.card title="Plan & perioadă">
                <div class="space-y-5">
                    <div>
                        <label for="plan_id" class="mb-1.5 block text-sm font-medium text-gray-700">Plan <span
                                class="text-red-500">*</span></label>
                        <select id="plan_id" name="plan_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="">— Selectează planul —</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan['id'] }}">
                                    {{ $plan['name'] }} — {{ $plan['price'] }} ({{ $plan['duration'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input label="Data start" name="start_date" type="date" required
                            value="{{ now()->format('Y-m-d') }}" />
                        <div>
                            <label for="end_date" class="mb-1.5 block text-sm font-medium text-gray-700">Data
                                sfârșit</label>
                            <input type="date" id="end_date" name="end_date" disabled
                                placeholder="Calculat automat"
                                class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500" />
                            <p class="mt-1 text-xs text-gray-500">Se calculează după plan (backend).</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Plată">
                <div class="space-y-5">
                    <div>
                        <label for="payment_method" class="mb-1.5 block text-sm font-medium text-gray-700">Metodă
                            plată <span class="text-red-500">*</span></label>
                        <select id="payment_method" name="payment_method" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="">— Selectează —</option>
                            @foreach ($paymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-ui.input label="Sumă încasată (RON)" name="amount" type="number" step="0.01" min="0"
                        placeholder="0.00" />

                    <div class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                        <input type="checkbox" id="create_invoice" name="create_invoice" value="1" checked
                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                        <label for="create_invoice" class="text-sm text-gray-700">
                            Generează factură automat la salvare
                        </label>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Note interne">
                <textarea id="notes" name="notes" rows="3" placeholder="Observații opționale…"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
            </x-ui.card>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-ui.button href="#" variant="secondary" size="lg">Anulează</x-ui.button>
                <x-ui.button type="submit" variant="primary" size="lg">
                    Creează abonament
                </x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
