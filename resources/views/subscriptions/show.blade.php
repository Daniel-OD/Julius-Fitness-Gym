@php
    use App\Enums\Status;
    use App\Helpers\Helpers;

    $subscriptionStatusColor = match ($subscription->status) {
        Status::Ongoing, Status::Renewed => 'green',
        Status::Expiring, Status::Upcoming => 'amber',
        Status::Expired, Status::Cancelled => 'red',
        default => 'gray',
    };

    $subscriptionStatusLabel = $subscription->status
        ? (__('app.status.' . $subscription->status->value) ?: $subscription->status->getLabel())
        : '—';

    $invoiceStatusColor = fn (?Status $status): string => match ($status) {
        Status::Paid => 'green',
        Status::Partial => 'amber',
        Status::Overdue => 'red',
        Status::Issued, Status::Pending => 'gray',
        Status::Cancelled, Status::Refund => 'red',
        default => 'gray',
    };

    $invoiceStatusLabel = fn (?Status $status): string => $status
        ? (__('app.status.' . $status->value) ?: $status->getLabel())
        : '—';
@endphp

<x-layouts.app :title="($subscription->plan?->name ?? __('app.resources.subscriptions.singular')) . ' · ' . config('app.name')">
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-sm text-white/45">
                    <a href="{{ route('web.subscriptions.index') }}" class="hover:text-brand-400">{{ __('app.resources.subscriptions.plural') }}</a>
                    <span>/</span>
                    <span class="text-white">{{ $subscription->plan?->name ?? __('app.resources.subscriptions.singular') }}</span>
                </nav>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-semibold tracking-tight text-white">
                        {{ $subscription->plan?->name ?? __('app.resources.subscriptions.singular') }}
                    </h1>
                    <x-ui.badge :color="$subscriptionStatusColor">{{ $subscriptionStatusLabel }}</x-ui.badge>
                </div>
                @if ($subscription->member)
                    <p class="mt-1 text-sm text-white/45">
                        <a href="{{ route('web.members.show', $subscription->member) }}" class="font-medium text-brand-400 hover:text-brand-300">
                            {{ $subscription->member->name }}
                        </a>
                        · {{ $subscription->member->code }}
                    </p>
                @endif
            </div>
            @if ($subscription->member)
                <x-ui.button :href="route('web.members.show', $subscription->member)" variant="secondary" size="md">
                    {{ __('app.resources.members.singular') }}
                </x-ui.button>
            @endif
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <x-ui.card :title="__('app.resources.subscriptions.singular')">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-white/35">{{ __('app.resources.plans.singular') }}</dt>
                        <dd class="mt-1 font-medium text-white">{{ $subscription->plan?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-white/35">{{ __('app.fields.start_date') }}</dt>
                        <dd class="mt-1 text-white">{{ $subscription->start_date?->translatedFormat('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-white/35">{{ __('app.fields.end_date') }}</dt>
                        <dd class="mt-1 text-white">{{ $subscription->end_date?->translatedFormat('d M Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-white/35">{{ __('app.fields.status') }}</dt>
                        <dd class="mt-1">
                            <x-ui.badge :color="$subscriptionStatusColor">{{ $subscriptionStatusLabel }}</x-ui.badge>
                        </dd>
                    </div>
                    <div class="border-t border-white/8 pt-4">
                        <dt class="text-xs font-medium uppercase tracking-wider text-white/35">{{ __('app.fields.amount') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">
                            {{ $subscription->plan ? Helpers::formatCurrency((float) $subscription->plan->amount) : '—' }}
                        </dd>
                        @if ($subscription->plan?->days)
                            <p class="mt-1 text-xs text-white/45">{{ $subscription->plan->days }} {{ strtolower(__('app.fields.days')) }}</p>
                        @endif
                    </div>
                </dl>
            </x-ui.card>
        </div>

        <div class="lg:col-span-2">
            <x-ui.card :padding="false" :title="__('app.resources.invoices.plural')">
                <x-slot name="subtitle">{{ $subscription->invoices->count() }} {{ strtolower(__('app.resources.invoices.plural')) }}</x-slot>

                @if ($subscription->invoices->isEmpty())
                    <div class="px-5 py-12 text-center text-sm text-white/45">
                        {{ __('app.empty.no_records', ['records' => strtolower(__('app.resources.invoices.plural'))]) }}
                    </div>
                @else
                    <x-ui.table :headings="[
                        __('app.fields.invoice_number'),
                        __('app.fields.date'),
                        __('app.fields.due_date'),
                        __('app.fields.total_amount'),
                        __('app.fields.paid_amount'),
                        __('app.fields.status'),
                    ]">
                        @foreach ($subscription->invoices->sortByDesc('date') as $invoice)
                            <tr class="hover:bg-white/5">
                                <td class="whitespace-nowrap px-5 py-4 font-medium text-white">
                                    {{ $invoice->number ?? '#' . $invoice->id }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-white/55">
                                    {{ $invoice->date?->translatedFormat('d M Y') ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-white/55">
                                    {{ $invoice->due_date?->translatedFormat('d M Y') ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-white">
                                    {{ Helpers::formatCurrency((float) ($invoice->total_amount ?? 0)) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-white/70">
                                    {{ Helpers::formatCurrency((float) ($invoice->paid_amount ?? 0)) }}
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :color="$invoiceStatusColor($invoice->status)">
                                        {{ $invoiceStatusLabel($invoice->status) }}
                                    </x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
