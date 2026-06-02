@php
    use App\Enums\Status;
    use App\Helpers\Helpers;

    $subscriptionStatusColor = fn (?Status $status): string => match ($status) {
        Status::Ongoing, Status::Renewed => 'green',
        Status::Expiring, Status::Upcoming => 'amber',
        Status::Expired, Status::Cancelled => 'red',
        default => 'gray',
    };

    $subscriptionStatusLabel = fn (?Status $status): string => $status
        ? (__('app.status.' . $status->value) ?: $status->getLabel())
        : '—';
@endphp

<x-layouts.app :title="__('app.resources.subscriptions.plural') . ' · ' . config('app.name')">
    <x-slot name="header">
        <div class="flex w-full flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('app.resources.subscriptions.plural') }}</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-white/45">{{ $subscriptions->total() }} {{ __('app.resources.subscriptions.plural') }}</p>
            </div>
            <x-ui.button :href="route('web.subscriptions.create')" variant="primary" size="md">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                {{ __('app.actions.new', ['resource' => strtolower(__('app.resources.subscriptions.singular'))]) }}
            </x-ui.button>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <x-ui.card :padding="false">
        <form method="GET" action="{{ route('web.subscriptions.index') }}"
            class="flex flex-col gap-4 border-b border-zinc-200 p-5 dark:border-white/8 sm:flex-row sm:items-end">
            <div class="w-full sm:w-56">
                <label for="status" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.fields.status') }}</label>
                <select id="status" name="status"
                    class="w-full rounded-lg border border-white/10 bg-surface-elevated px-3 py-2 text-sm text-white focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Toate</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="expired" @selected(request('status') === 'expired')>{{ __('app.status.expired') }}</option>
                </select>
            </div>
            <x-ui.button type="submit" variant="secondary" size="md" class="shrink-0">
                {{ __('app.dashboard.actions.apply') ?? 'Filtrează' }}
            </x-ui.button>
        </form>

        <x-ui.table :headings="[
            __('app.resources.members.singular'),
            __('app.resources.plans.singular'),
            __('app.fields.start_date'),
            __('app.fields.end_date'),
            __('app.fields.status'),
            __('app.fields.amount'),
            '',
        ]">
            @forelse ($subscriptions as $subscription)
                <tr class="transition-colors hover:bg-white/5">
                    <td class="whitespace-nowrap px-5 py-4">
                        @if ($subscription->member)
                            <a href="{{ route('web.members.show', $subscription->member) }}"
                                class="font-medium text-white hover:text-brand-300">
                                {{ $subscription->member->name }}
                            </a>
                            <div class="text-xs text-white/45">{{ $subscription->member->code }}</div>
                        @else
                            <span class="text-sm text-white/45">—</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-white/70">
                        {{ $subscription->plan?->name ?? '—' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-white/55">
                        {{ $subscription->start_date?->translatedFormat('d M Y') ?? '—' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-white/55">
                        {{ $subscription->end_date?->translatedFormat('d M Y') ?? '—' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <x-ui.badge :color="$subscriptionStatusColor($subscription->status)">
                            {{ $subscriptionStatusLabel($subscription->status) }}
                        </x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-white">
                        {{ $subscription->plan ? Helpers::formatCurrency((float) $subscription->plan->amount) : '—' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-right">
                        <a href="{{ route('web.subscriptions.show', $subscription) }}"
                            class="text-sm font-medium text-brand-400 hover:text-brand-300">{{ __('app.actions.view') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-0">
                        <x-ui.empty-state :title="__('app.empty.no_subscriptions')" />
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($subscriptions->hasPages())
            <div class="border-t border-zinc-200 px-5 py-4 dark:border-white/8">
                {{ $subscriptions->withQueryString()->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>
