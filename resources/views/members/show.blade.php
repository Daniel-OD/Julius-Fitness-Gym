@php
    use App\Enums\Status;
    use App\Helpers\Helpers;
    use Illuminate\Support\Facades\Storage;

    $memberInitials = collect(preg_split('/\s+/', trim($member->name ?? '')) ?: [])
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: '?';

    $memberStatusColor = match ($member->status) {
        Status::Active => 'green',
        Status::Inactive => 'gray',
        default => 'gray',
    };

    $subscriptionStatusColor = fn (?Status $status): string => match ($status) {
        Status::Ongoing, Status::Renewed => 'green',
        Status::Expiring, Status::Upcoming => 'amber',
        Status::Expired, Status::Cancelled => 'red',
        default => 'gray',
    };

    $activeSubscriptions = $member->subscriptions->whereIn('status', [
        Status::Ongoing,
        Status::Expiring,
        Status::Upcoming,
    ])->count();
@endphp

<x-layouts.app :title="$member->name . ' · ' . __('app.resources.members.plural')">
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                @if ($member->photo)
                    <img src="{{ Storage::disk('public')->url($member->photo) }}" alt=""
                        class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white shadow-sm" />
                @else
                    <span
                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-600 text-xl font-semibold text-white shadow-sm">
                        {{ $memberInitials }}
                    </span>
                @endif
                <div>
                    <nav class="mb-1 flex items-center gap-2 text-sm text-gray-500">
                        <a href="{{ route('web.members.index') }}" class="hover:text-brand-600">{{ __('app.resources.members.plural') }}</a>
                        <span>/</span>
                        <span class="text-gray-900">{{ $member->name }}</span>
                    </nav>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ $member->name }}</h1>
                        <x-ui.badge :color="$memberStatusColor">{{ $member->status?->getLabel() }}</x-ui.badge>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $member->code }} · {{ $member->created_at?->translatedFormat('d M Y') }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="route('web.members.edit', $member)" variant="secondary" size="md">
                    {{ __('app.actions.edit', ['resource' => __('app.resources.members.singular')]) }}
                </x-ui.button>
                <x-ui.button :href="route('web.subscriptions.create', ['member_id' => $member->id])" variant="primary" size="md">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    {{ __('app.actions.new', ['resource' => __('app.resources.subscriptions.singular')]) }}
                </x-ui.button>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-1">
            <x-ui.card :title="__('app.fields.contact')">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ __('app.fields.email') }}</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $member->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ __('app.fields.contact') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $member->contact ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ __('app.fields.emergency_contact') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $member->emergency_contact ?? '—' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card title="{{ __('app.fields.details') ?? 'Detalii' }}">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('app.fields.gender') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $member->gender ? ucfirst($member->gender) : '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('app.fields.dob') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $member->dob?->translatedFormat('d M Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">{{ __('app.fields.source') }}</dt>
                        <dd class="font-medium text-gray-900">{{ $member->source ? str_replace('_', ' ', $member->source) : '—' }}</dd>
                    </div>
                    @if ($member->address)
                        <div class="border-t border-gray-100 pt-3">
                            <dt class="text-gray-500">{{ __('app.fields.address') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $member->address }}</dd>
                        </div>
                    @endif
                </dl>
            </x-ui.card>
        </div>

        <div class="lg:col-span-2">
            <x-ui.card :padding="false" :title="__('app.resources.subscriptions.plural')">
                <x-slot name="actions">
                    <x-ui.button :href="route('web.subscriptions.create', ['member_id' => $member->id])" variant="primary" size="sm">
                        {{ __('app.actions.new', ['resource' => __('app.resources.subscriptions.singular')]) }}
                    </x-ui.button>
                </x-slot>
                <x-slot name="subtitle">{{ $member->subscriptions->count() }} total</x-slot>

                @if ($member->subscriptions->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm text-gray-500">{{ __('app.empty.create_to_get_started', ['resource' => __('app.resources.subscriptions.singular')]) }}</p>
                        <x-ui.button :href="route('web.subscriptions.create', ['member_id' => $member->id])" variant="primary" size="md" class="mt-4">
                            {{ __('app.actions.new', ['resource' => __('app.resources.subscriptions.singular')]) }}
                        </x-ui.button>
                    </div>
                @else
                    <x-ui.table :headings="[__('app.resources.plans.singular'), __('app.fields.period') ?? 'Perioadă', __('app.fields.amount'), __('app.fields.status'), '']">
                        @foreach ($member->subscriptions->sortByDesc('end_date') as $subscription)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-5 py-4 font-medium text-gray-900">
                                    {{ $subscription->plan?->name ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600">
                                    {{ $subscription->start_date?->translatedFormat('d M Y') }}
                                    —
                                    {{ $subscription->end_date?->translatedFormat('d M Y') }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-900">
                                    {{ $subscription->plan ? Helpers::formatCurrency((float) $subscription->plan->amount) : '—' }}
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge :color="$subscriptionStatusColor($subscription->status)">
                                        {{ $subscription->status?->getLabel() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('web.subscriptions.show', $subscription) }}"
                                        class="text-sm font-medium text-brand-600 hover:text-brand-700">{{ __('app.actions.view') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </x-ui.card>

            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <x-ui.stat-card :label="__('app.widgets.active_members')" :value="(string) $activeSubscriptions" color="green" />
                <x-ui.stat-card :label="__('app.resources.subscriptions.plural')" :value="(string) $member->subscriptions->count()" color="brand" />
                <x-ui.stat-card :label="__('app.status.expired')" :value="(string) $member->subscriptions->where('status', Status::Expired)->count()" color="red" />
            </div>
        </div>
    </div>
</x-layouts.app>
