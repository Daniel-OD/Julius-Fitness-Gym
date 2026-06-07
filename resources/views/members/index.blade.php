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
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('app.resources.members.plural') }}</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-white/45">{{ $members->total() }} {{ __('app.resources.members.plural') }}</p>
            </div>
            <x-ui.button :href="route('web.members.create')" variant="primary" size="md">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                Adaugă {{ strtolower(__('app.resources.members.singular')) }}
            </x-ui.button>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <x-ui.card :padding="false">
        <form method="GET" action="{{ route('web.members.index') }}"
            class="flex flex-col gap-4 border-b border-zinc-200 p-5 dark:border-white/8 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="search" class="mb-1.5 block text-sm font-medium text-white/70">Caută</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/35"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input type="search" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Nume, cod, email…"
                        class="w-full rounded-lg border border-white/10 bg-surface-elevated py-2 pl-9 pr-3 text-sm text-white placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </div>
            </div>
            <div class="w-full sm:w-48">
                <label for="status" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.fields.status') }}</label>
                <select id="status" name="status"
                    class="w-full rounded-lg border border-white/10 bg-surface-elevated px-3 py-2 text-sm text-white focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Toți</option>
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
                <tr class="transition-colors hover:bg-white/5">
                    <td class="whitespace-nowrap px-5 py-4">
                        <a href="{{ route('web.members.show', $member) }}" class="group flex items-center gap-3">
                            @if ($member->photo)
                                <img src="{{ Storage::disk('public')->url($member->photo) }}" alt=""
                                    class="h-10 w-10 rounded-full object-cover ring-2 ring-white" />
                            @else
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-500/100/15 text-sm font-semibold text-brand-300 ring-2 ring-white">
                                    {{ $memberInitials($member->name) }}
                                </span>
                            @endif
                            <span class="min-w-0">
                                <span
                                    class="block truncate font-medium text-white group-hover:text-brand-300">{{ $member->name }}</span>
                                <span class="block truncate text-xs text-white/45">{{ $member->code }}</span>
                            </span>
                        </a>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-sm text-white">{{ $member->email ?? '—' }}</div>
                        <div class="text-xs text-white/45">{{ $member->contact ?? '—' }}</div>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-white/70">
                        {{ $planName }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-white/45">
                        {{ $member->created_at?->translatedFormat('d M Y') }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <x-ui.badge :color="$statusBadgeColor($member->status)">
                            {{ $member->status?->getLabel() ?? '—' }}
                        </x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('web.members.qr', $member) }}"
                                class="text-sm font-medium text-white/55 hover:text-white"
                                title="{{ __('app.members.qr.title') }}">
                                QR
                            </a>
                            <a href="{{ route('web.members.show', $member) }}"
                                class="text-sm font-medium text-brand-400 hover:text-brand-300">{{ __('app.actions.view') }}</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-0">
                        <x-ui.empty-state :title="__('app.empty.no_members')" />
                    </td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($members->hasPages())
            <div class="border-t border-zinc-200 px-5 py-4 dark:border-white/8">
                {{ $members->withQueryString()->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>
