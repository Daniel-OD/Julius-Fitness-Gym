@php
    use App\Enums\Status;
    use Illuminate\Support\Facades\Storage;

    $statusBadgeColor = fn (?Status $status): string => match ($status) {
        Status::Active => 'green',
        Status::Inactive => 'gray',
        default => 'gray',
    };

    $memberInitials = function (?string $name): string {
        $parts = preg_split('/\s+/', trim($name ?? '')) ?: [];
        $letters = collect($parts)->take(2)->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)));

        return $letters->isNotEmpty() ? $letters->implode('') : '?';
    };
@endphp

<x-layouts.app :title="__('app.resources.members.plural') . ' · ' . config('app.name')">
    <x-slot name="header">
        <div class="flex w-full flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ __('app.resources.members.plural') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $members->total() }} {{ __('app.resources.members.plural') }}</p>
            </div>
            <x-ui.button :href="route('web.members.create')" variant="primary" size="md">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                {{ __('app.actions.new', ['resource' => __('app.resources.members.singular')]) }}
            </x-ui.button>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <x-ui.card :padding="false">
        <form method="GET" action="{{ route('web.members.index') }}"
            class="flex flex-col gap-4 border-b border-gray-100 p-5 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('app.fields.search') ?? 'Caută' }}</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input type="search" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Nume, cod, email…"
                        class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </div>
            </div>
            <div class="w-full sm:w-48">
                <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('app.fields.status') }}</label>
                <select id="status" name="status"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">{{ __('app.status.all') ?? 'Toți' }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('app.status.active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('app.status.inactive') }}</option>
                </select>
            </div>
            <x-ui.button type="submit" variant="secondary" size="md" class="shrink-0">
                {{ __('app.dashboard.actions.apply') ?? 'Filtrează' }}
            </x-ui.button>
        </form>

        <x-ui.table :headings="[__('app.resources.members.singular'), __('app.fields.contact'), __('app.resources.plans.singular'), __('app.fields.date'), __('app.fields.status'), '']">
            @forelse ($members as $member)
                @php
                    $latestSubscription = $member->subscriptions->sortByDesc('end_date')->first();
                    $planName = $latestSubscription?->plan?->name ?? '—';
                @endphp
                <tr class="transition-colors hover:bg-gray-50/80">
                    <td class="whitespace-nowrap px-5 py-4">
                        <a href="{{ route('web.members.show', $member) }}" class="group flex items-center gap-3">
                            @if ($member->photo)
                                <img src="{{ Storage::disk('public')->url($member->photo) }}" alt=""
                                    class="h-10 w-10 rounded-full object-cover ring-2 ring-white" />
                            @else
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700 ring-2 ring-white">
                                    {{ $memberInitials($member->name) }}
                                </span>
                            @endif
                            <span class="min-w-0">
                                <span
                                    class="block truncate font-medium text-gray-900 group-hover:text-brand-700">{{ $member->name }}</span>
                                <span class="block truncate text-xs text-gray-500">{{ $member->code }}</span>
                            </span>
                        </a>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-sm text-gray-900">{{ $member->email ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $member->contact ?? '—' }}</div>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-700">
                        {{ $planName }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-500">
                        {{ $member->created_at?->translatedFormat('d M Y') }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <x-ui.badge :color="$statusBadgeColor($member->status)">
                            {{ $member->status?->getLabel() ?? '—' }}
                        </x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-right">
                        <a href="{{ route('web.members.show', $member) }}"
                            class="text-sm font-medium text-brand-600 hover:text-brand-700">{{ __('app.actions.view', ['resource' => '']) ?: 'Vezi' }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">
                        {{ __('app.empty.no_records', ['records' => __('app.resources.members.plural')]) }}
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($members->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $members->withQueryString()->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>
