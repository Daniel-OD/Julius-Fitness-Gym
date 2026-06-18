@php
    $initialMode = $mode ?? 'login';

    if ($errors->has('name') || $errors->has('contact') || filled(old('name'))) {
        $initialMode = 'register';
    }
@endphp

<x-layouts.minimal :title="__($initialMode === 'register' ? 'app.member.auth.register' : 'app.member.auth.login')">
    <div x-data="{ mode: '{{ $initialMode }}' }" class="mx-auto w-full max-w-md">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-zinc-950 sm:p-8">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-zinc-500 dark:text-zinc-400">Julius Fitness</p>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    <span x-show="mode === 'login'" x-cloak>{{ __('app.member.auth.login') }}</span>
                    <span x-show="mode === 'register'" x-cloak>{{ __('app.member.auth.register') }}</span>
                </h1>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    <span x-show="mode === 'login'" x-cloak>{{ __('app.member.auth.login_subtitle') }}</span>
                    <span x-show="mode === 'register'" x-cloak>{{ __('app.member.auth.register_subtitle') }}</span>
                </p>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-1 rounded-2xl bg-zinc-100 p-1 dark:bg-white/5" role="tablist">
                <button type="button" role="tab" :aria-selected="mode === 'login'"
                    @click="mode = 'login'"
                    class="rounded-xl px-3 py-2.5 text-sm font-medium transition"
                    :class="mode === 'login' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'">
                    {{ __('app.member.auth.login') }}
                </button>
                <button type="button" role="tab" :aria-selected="mode === 'register'"
                    @click="mode = 'register'"
                    class="rounded-xl px-3 py-2.5 text-sm font-medium transition"
                    :class="mode === 'register' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-white' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'">
                    {{ __('app.member.auth.register') }}
                </button>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800/30 dark:bg-emerald-950/50 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if (! empty($intendedPlan))
                <div class="mt-6 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-900 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-100">
                    {{ __('app.member.plans.intended', ['plan' => $intendedPlan->name]) }}
                </div>
            @endif

            <div x-show="mode === 'register'" x-cloak class="mt-6 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 dark:border-white/10 dark:bg-white/5 dark:text-zinc-400">
                {{ __('app.member.auth.register_verify_hint') }}
            </div>

            <div class="mt-6" x-show="mode === 'login'" x-cloak style="display: {{ $initialMode === 'login' ? 'block' : 'none' }};">
                <form method="POST" action="{{ route('member.login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="login-email" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('app.fields.email') }}
                        </label>
                        <input id="login-email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100 dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400" />
                        @error('email')
                            @if (! $errors->has('name'))
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>

                    <div>
                        <label for="login-password" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('app.fields.password') }}
                        </label>
                        <input id="login-password" name="password" type="password" required autocomplete="current-password"
                            class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100 dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400" />
                        @error('password')
                            @if (! $errors->has('name'))
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-zinc-300 text-brand-600 focus:ring-brand-500" />
                        {{ __('app.member.auth.remember_me') }}
                    </label>

                    <x-ui.button type="submit" variant="primary" size="md" class="w-full">
                        {{ __('app.member_portal.login_button') }}
                    </x-ui.button>

                    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                        <a href="{{ route('member.password.request') }}" class="font-medium text-zinc-900 hover:underline dark:text-white">
                            {{ __('app.member_portal.forgot_password_link') }}
                        </a>
                    </p>
                </form>
            </div>

            <div class="mt-6" x-show="mode === 'register'" x-cloak style="display: {{ $initialMode === 'register' ? 'block' : 'none' }};">
                <form method="POST" action="{{ route('member.register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="register-name" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('app.fields.name') }}
                        </label>
                        <input id="register-name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name"
                            class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100 dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400" />
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="register-email" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('app.fields.email') }}
                        </label>
                        <input id="register-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                            class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100 dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400" />
                        @error('email')
                            @if ($errors->has('name') || $errors->has('contact'))
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>

                    <div>
                        <label for="register-contact" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('app.fields.contact') }}
                        </label>
                        <input id="register-contact" name="contact" type="tel" value="{{ old('contact') }}" required autocomplete="tel"
                            class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100 dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400" />
                        @error('contact')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="register-password" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('app.fields.password') }}
                        </label>
                        <input id="register-password" name="password" type="password" required autocomplete="new-password"
                            class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100 dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400" />
                        @error('password')
                            @if ($errors->has('name') || $errors->has('contact'))
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @endif
                        @enderror
                    </div>

                    <div>
                        <label for="register-password-confirmation" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('app.fields.confirm_password') }}
                        </label>
                        <input id="register-password-confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                            class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100 dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400" />
                    </div>

                    <x-ui.button type="submit" variant="primary" size="md" class="w-full">
                        {{ __('app.member.auth.create_account') }}
                    </x-ui.button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.minimal>
