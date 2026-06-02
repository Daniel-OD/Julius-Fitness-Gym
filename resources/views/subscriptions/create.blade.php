@php
    use App\Enums\Status;
    use App\Helpers\Helpers;

    $memberInitials = collect(preg_split('/\s+/', trim($selectedMember?->name ?? '')) ?: [])
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: '?';

    $subscriptionStatuses = [
        Status::Upcoming,
        Status::Ongoing,
        Status::Expiring,
        Status::Expired,
        Status::Renewed,
        Status::Cancelled,
    ];
@endphp

<x-layouts.app :title="__('app.actions.new', ['resource' => __('app.resources.subscriptions.singular')]) . ' · ' . config('app.name')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-white/45">
                    <a href="{{ route('web.members.index') }}" class="hover:text-brand-400">{{ __('app.resources.members.plural') }}</a>
                    @if ($selectedMember)
                        <span>/</span>
                        <a href="{{ route('web.members.show', $selectedMember) }}" class="hover:text-brand-400">{{ $selectedMember->name }}</a>
                    @endif
                    <span>/</span>
                    <span class="text-white">{{ __('app.actions.new', ['resource' => __('app.resources.subscriptions.singular')]) }}</span>
                </nav>
                <h1 class="text-2xl font-semibold tracking-tight text-white">
                    {{ __('app.actions.new', ['resource' => __('app.resources.subscriptions.singular')]) }}
                </h1>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-2xl space-y-6">
        <form action="{{ route('web.subscriptions.store') }}" method="POST" data-jf-form class="space-y-6">
            @csrf

            <x-ui.card :title="__('app.resources.members.singular')">
                @if ($selectedMember)
                    <div class="flex items-center gap-4 rounded-xl border border-brand-500/20 bg-brand-500/10/60 px-4 py-3">
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-500 text-sm font-semibold text-white">
                            {{ $memberInitials }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-white">{{ $selectedMember->name }}</p>
                            <p class="text-sm text-white/55">{{ $selectedMember->code }}</p>
                        </div>
                        <a href="{{ route('web.subscriptions.create') }}"
                            class="shrink-0 text-sm font-medium text-brand-400 hover:text-brand-300">Schimbă</a>
                    </div>
                    <input type="hidden" name="member_id" value="{{ $selectedMember->id }}" />
                @else
                    <div>
                        <label for="member_id" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.fields.member') }} <span class="text-red-500">*</span></label>
                        <select id="member_id" name="member_id" required
                            class="w-full rounded-lg border border-white/10 bg-surface-elevated px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="">— {{ __('app.fields.member') }} —</option>
                            @foreach ($members as $m)
                                <option value="{{ $m->id }}" @selected(old('member_id') == $m->id)>{{ $m->name }} ({{ $m->code }})</option>
                            @endforeach
                        </select>
                        @error('member_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card :title="__('app.resources.plans.singular') . ' & ' . (__('app.fields.period') ?? 'perioadă')">
                <div class="space-y-5">
                    <div>
                        <label for="plan_id" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.resources.plans.singular') }} <span class="text-red-500">*</span></label>
                        <select id="plan_id" name="plan_id" required
                            class="w-full rounded-lg border border-white/10 bg-surface-elevated px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="">—</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                    {{ $plan->name }} — {{ Helpers::formatCurrency((float) $plan->amount) }}
                                    ({{ $plan->days }} {{ __('app.units.days') ?? 'zile' }})
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-ui.input :label="__('app.fields.start_date')" name="start_date" type="date" required
                            :value="old('start_date', now()->format('Y-m-d'))" :error="$errors->first('start_date')" />
                        <x-ui.input :label="__('app.fields.end_date')" name="end_date" type="date"
                            :value="old('end_date')" :error="$errors->first('end_date')" />
                    </div>
                    <p class="text-xs text-white/45">{{ __('app.help.subscription_end_date') ?? 'Data de sfârșit se calculează automat din plan dacă e goală.' }}</p>

                    <div>
                        <label for="status" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.fields.status') }}</label>
                        <select id="status" name="status"
                            class="w-full rounded-lg border border-white/10 bg-surface-elevated px-3 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            @foreach ($subscriptionStatuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', Status::Ongoing->value) === $status->value)>
                                    {{ $status->getLabel() }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-ui.card>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-ui.button
                    :href="$selectedMember ? route('web.members.show', $selectedMember) : route('web.subscriptions.index')"
                    variant="secondary" size="lg">{{ __('app.actions.cancel') }}</x-ui.button>
                <x-ui.button type="submit" variant="primary" size="lg" data-jf-submit>{{ __('app.actions.save') }}</x-ui.button>
            </div>
        </form>
    </div>
</x-layouts.app>
