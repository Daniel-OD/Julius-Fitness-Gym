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
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ __('app.resources.subscriptions.plural') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $subscriptions->total() }} {{ __('app.resources.subscriptions.plural') }}</p>
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
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <x-ui.card :padding="false">
        <form method="GET" action="{{ route('web.subscriptions.index') }}"
            class="flex flex-col gap-4 border-b border-gray-100 p-5 sm:flex-row sm:items-end">
            <div class="w-full sm:w-56">
                <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('app.fields.status') }}</label>
                <select id="status" name="status"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
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
                <tr class="transition-colors hover:bg-gray-50/80">
                    <td class="whitespace-nowrap px-5 py-4">
                        @if ($subscription->member)
                            <a href="{{ route('web.members.show', $subscription->member) }}"
                                class="font-medium text-gray-900 hover:text-brand-700">
                                {{ $subscription->member->name }}
                            </a>
                            <div class="text-xs text-gray-500">{{ $subscription->member->code }}</div>
                        @else
                            <span class="text-sm text-gray-500">—</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                        {{ $subscription->plan?->name ?? '—' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                        {{ $subscription->start_date?->translatedFormat('d M Y') ?? '—' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                        {{ $subscription->end_date?->translatedFormat('d M Y') ?? '—' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <x-ui.badge :color="$subscriptionStatusColor($subscription->status)">
                            {{ $subscriptionStatusLabel($subscription->status) }}
                        </x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-900">
                        {{ $subscription->plan ? Helpers::formatCurrency((float) $subscription->plan->amount) : '—' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-right">
                        <a href="{{ route('web.subscriptions.show', $subscription) }}"
                            class="text-sm font-medium text-brand-600 hover:text-brand-700">{{ __('app.actions.view') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-500">
                        {{ __('app.empty.no_records', ['records' => __('app.resources.subscriptions.plural')]) }}
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($subscriptions->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $subscriptions->withQueryString()->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>
