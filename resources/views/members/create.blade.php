<x-layouts.app :title="__('app.actions.new', ['resource' => __('app.resources.members.singular')]) . ' · ' . config('app.name')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-white/45">
                    <a href="{{ route('web.members.index') }}" class="hover:text-brand-400">{{ __('app.resources.members.plural') }}</a>
                    <span>/</span>
                    <span class="text-white">{{ __('app.actions.new', ['resource' => __('app.resources.members.singular')]) }}</span>
                </nav>
                <h1 class="text-2xl font-semibold tracking-tight text-white">
                    {{ __('app.actions.new', ['resource' => __('app.resources.members.singular')]) }}
                </h1>
            </div>
            <x-ui.button :href="route('web.members.index')" variant="ghost" size="md">{{ __('app.actions.cancel') }}</x-ui.button>
        </div>
    </x-slot>

    <form action="{{ route('web.members.store') }}" method="POST" enctype="multipart/form-data" data-jf-form
        class="mx-auto max-w-3xl space-y-6">
        @csrf

        <x-ui.card title="Poză profil" subtitle="Opțional — JPG sau PNG">
            <label
                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-white/10 bg-white/5 px-6 py-8 transition-colors hover:border-brand-300 hover:bg-brand-500/10">
                <svg class="mb-2 h-8 w-8 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" x2="12" y1="3" y2="15" />
                </svg>
                <span class="text-sm font-medium text-white/70">Alege imagine</span>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only" />
            </label>
            <x-ui.field-error :message="$errors->first('photo')" class="mt-2" />
        </x-ui.card>

        <x-ui.card title="{{ __('app.fields.details') ?? 'Date personale' }}">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input label="{{ __('app.fields.name') }}" name="name" :value="old('name')" required
                    :error="$errors->first('name')" />
                <x-ui.input label="{{ __('app.fields.code') }}" name="code" :value="old('code')"
                    placeholder="Auto" :error="$errors->first('code')" />
                <x-ui.input label="{{ __('app.fields.email') }}" name="email" type="email" :value="old('email')"
                    :error="$errors->first('email')" />
                <x-ui.input label="{{ __('app.fields.contact') }}" name="contact" type="tel" :value="old('contact')"
                    :error="$errors->first('contact')" />
                <x-ui.input label="{{ __('app.fields.emergency_contact') }}" name="emergency_contact" type="tel"
                    :value="old('emergency_contact')" :error="$errors->first('emergency_contact')" />
                <div>
                    <label for="gender" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.fields.gender') }}</label>
                    <select id="gender" name="gender"
                        class="w-full rounded-lg border border-white/10 bg-surface-elevated px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">—</option>
                        <option value="male" @selected(old('gender') === 'male')>{{ __('app.gender.male') ?? 'Masculin' }}</option>
                        <option value="female" @selected(old('gender') === 'female')>{{ __('app.gender.female') ?? 'Feminin' }}</option>
                        <option value="other" @selected(old('gender') === 'other')>Altul</option>
                    </select>
                </div>
                <x-ui.input label="{{ __('app.fields.dob') }}" name="dob" type="date" :value="old('dob')"
                    :error="$errors->first('dob')" />
                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.fields.status') }}</label>
                    <select id="status" name="status"
                        class="w-full rounded-lg border border-white/10 bg-surface-elevated px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="active" @selected(old('status', 'active') === 'active')>{{ __('app.status.active') }}</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>{{ __('app.status.inactive') }}</option>
                    </select>
                </div>
                <div>
                    <label for="source" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.fields.source') }}</label>
                    <select id="source" name="source"
                        class="w-full rounded-lg border border-white/10 bg-surface-elevated px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">—</option>
                        <option value="word_of_mouth" @selected(old('source') === 'word_of_mouth')>Recomandare</option>
                        <option value="promotions" @selected(old('source') === 'promotions')>Promoții</option>
                        <option value="others" @selected(old('source') === 'others')>Altele</option>
                    </select>
                </div>
                <div>
                    <label for="goal" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.fields.goal') ?? 'Obiectiv' }}</label>
                    <select id="goal" name="goal"
                        class="w-full rounded-lg border border-white/10 bg-surface-elevated px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">—</option>
                        @foreach (['fitness', 'fatloss', 'weightgain', 'body_building', 'others'] as $goal)
                            <option value="{{ $goal }}" @selected(old('goal') === $goal)>{{ ucfirst(str_replace('_', ' ', $goal)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="mb-1.5 block text-sm font-medium text-white/70">{{ __('app.fields.address') }}</label>
                    <textarea id="address" name="address" rows="2"
                        class="w-full rounded-lg border border-white/10 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">{{ old('address') }}</textarea>
                </div>
            </div>
        </x-ui.card>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-ui.button :href="route('web.members.index')" variant="secondary" size="lg">{{ __('app.actions.cancel') }}</x-ui.button>
            <x-ui.button type="submit" variant="primary" size="lg" data-jf-submit>{{ __('app.actions.save') }}</x-ui.button>
        </div>
    </form>
</x-layouts.app>
