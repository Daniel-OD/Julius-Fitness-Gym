<x-layouts.app :title="__('app.actions.edit', ['resource' => __('app.resources.members.singular')]) . ' · ' . config('app.name')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-gray-500">
                    <a href="{{ route('web.members.index') }}" class="hover:text-brand-600">{{ __('app.resources.members.plural') }}</a>
                    <span>/</span>
                    <a href="{{ route('web.members.show', $member) }}" class="hover:text-brand-600">{{ $member->name }}</a>
                    <span>/</span>
                    <span class="text-gray-900">{{ __('app.actions.edit') }}</span>
                </nav>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900">
                    {{ __('app.actions.edit', ['resource' => __('app.resources.members.singular')]) }}
                </h1>
            </div>
            <x-ui.button :href="route('web.members.show', $member)" variant="ghost" size="md">{{ __('app.actions.cancel') }}</x-ui.button>
        </div>
    </x-slot>

    <form action="{{ route('web.members.update', $member) }}" method="POST" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @method('PUT')

        <x-ui.card title="{{ __('app.fields.details') ?? 'Date personale' }}">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="{{ __('app.fields.name') }}" name="name" :value="old('name', $member->name)" required
                    :error="$errors->first('name')" />
                <x-ui.input label="{{ __('app.fields.code') }}" name="code" :value="old('code', $member->code)" readonly
                    class="bg-gray-50" />
                <x-ui.input label="{{ __('app.fields.email') }}" name="email" type="email" :value="old('email', $member->email)"
                    :error="$errors->first('email')" />
                <x-ui.input label="{{ __('app.fields.contact') }}" name="contact" type="tel"
                    :value="old('contact', $member->contact)" :error="$errors->first('contact')" />
                <x-ui.input label="{{ __('app.fields.emergency_contact') }}" name="emergency_contact" type="tel"
                    :value="old('emergency_contact', $member->emergency_contact)" />
                <div>
                    <label for="gender" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('app.fields.gender') }}</label>
                    <select id="gender" name="gender"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">—</option>
                        <option value="male" @selected(old('gender', $member->gender) === 'male')>Masculin</option>
                        <option value="female" @selected(old('gender', $member->gender) === 'female')>Feminin</option>
                        <option value="other" @selected(old('gender', $member->gender) === 'other')>Altul</option>
                    </select>
                </div>
                <x-ui.input label="{{ __('app.fields.dob') }}" name="dob" type="date"
                    :value="old('dob', $member->dob?->format('Y-m-d'))" />
                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('app.fields.status') }}</label>
                    <select id="status" name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="active" @selected(old('status', $member->status?->value) === 'active')>{{ __('app.status.active') }}</option>
                        <option value="inactive" @selected(old('status', $member->status?->value) === 'inactive')>{{ __('app.status.inactive') }}</option>
                    </select>
                </div>
                <div>
                    <label for="source" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('app.fields.source') }}</label>
                    <select id="source" name="source"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">—</option>
                        <option value="word_of_mouth" @selected(old('source', $member->source) === 'word_of_mouth')>Recomandare</option>
                        <option value="promotions" @selected(old('source', $member->source) === 'promotions')>Promoții</option>
                        <option value="others" @selected(old('source', $member->source) === 'others')>Altele</option>
                    </select>
                </div>
                <div>
                    <label for="goal" class="mb-1.5 block text-sm font-medium text-gray-700">Obiectiv</label>
                    <select id="goal" name="goal"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">—</option>
                        @foreach (['fitness', 'fatloss', 'weightgain', 'body_building', 'others'] as $goal)
                            <option value="{{ $goal }}" @selected(old('goal', $member->goal) === $goal)>{{ ucfirst(str_replace('_', ' ', $goal)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('app.fields.address') }}</label>
                    <textarea id="address" name="address" rows="2"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">{{ old('address', $member->address) }}</textarea>
                </div>
            </div>
        </x-ui.card>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-ui.button :href="route('web.members.show', $member)" variant="secondary" size="lg">{{ __('app.actions.cancel') }}</x-ui.button>
            <x-ui.button type="submit" variant="primary" size="lg">{{ __('app.actions.save') }}</x-ui.button>
        </div>
    </form>

    <form action="{{ route('web.members.destroy', $member) }}" method="POST" class="mx-auto mt-6 max-w-3xl"
        onsubmit="return confirm('Ștergi acest membru?');">
        @csrf
        @method('DELETE')
        <x-ui.button type="submit" variant="danger" size="lg">Șterge membru</x-ui.button>
    </form>
</x-layouts.app>
