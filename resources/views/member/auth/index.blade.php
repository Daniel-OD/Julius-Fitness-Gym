@php
    $initialMode = $mode ?? 'login';

    if ($errors->has('name') || $errors->has('contact') || filled(old('name'))) {
        $initialMode = 'register';
    }
@endphp

<x-layouts.minimal :title="__($initialMode === 'register' ? 'app.member.auth.register' : 'app.member.auth.login')">
    <div x-data="{ mode: '{{ $initialMode }}' }" class="jf-safe-x jf-safe-b mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-3xl">
            <div class="rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-zinc-950">
                <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
                    <aside class="space-y-6 border-b border-zinc-200 p-6 dark:border-white/10 lg:border-b-0 lg:border-r lg:p-8">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-zinc-500 dark:text-zinc-400">Julius Fitness</p>
                            <h1 class="mt-4 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                                <span x-show="mode === 'login'" x-cloak>{{ __('app.member.auth.login') }}</span>
                                <span x-show="mode === 'register'" x-cloak>{{ __('app.member.auth.register') }}</span>
                            </h1>
                            <p class="mt-3 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                                <span x-show="mode === 'login'" x-cloak>{{ __('app.member.auth.login_subtitle') }}</span>
                                <span x-show="mode === 'register'" x-cloak>{{ __('app.member.auth.register_subtitle') }}</span>
                            </p>
                        </div>

                        <div class="space-y-3" role="tablist" aria-label="{{ __('app.member.auth.login') }} / {{ __('app.member.auth.register') }}">
                            <button type="button" role="tab" :aria-selected="mode === 'login'"
                                @click="mode = 'login'"
                                class="block w-full rounded-2xl px-4 py-3 text-left text-sm font-medium transition"
                                :class="mode === 'login' ? 'bg-brand-500 text-white shadow-sm ring-2 ring-brand-500/30' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-white/5 dark:text-white dark:hover:bg-white/10'">
                                {{ __('app.member.auth.login') }}
                            </button>
                            <button type="button" role="tab" :aria-selected="mode === 'register'"
                                @click="mode = 'register'"
                                class="block w-full rounded-2xl px-4 py-3 text-left text-sm font-medium transition"
                                :class="mode === 'register' ? 'bg-brand-500 text-white shadow-sm ring-2 ring-brand-500/30' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-white/5 dark:text-white dark:hover:bg-white/10'">
                                {{ __('app.member.auth.register') }}
                            </button>
                        </div>

                        <div class="rounded-3xl bg-zinc-50 p-5 text-sm text-zinc-700 dark:bg-white/5 dark:text-zinc-300">
                            <p class="font-semibold">{{ __('app.member.auth.verify_email') }}</p>
                            <p class="mt-2 leading-6 text-zinc-600 dark:text-zinc-400">{{ __('app.member.auth.verify_email_hint') }}</p>
                        </div>
                    </aside>

                    <main class="p-6 sm:p-8">
                        @if (session('status'))
                            <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800/30 dark:bg-emerald-950/50 dark:text-emerald-200">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div x-show="mode === 'login'" x-cloak style="display: {{ $initialMode === 'login' ? 'block' : 'none' }};">
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

                                <div class="flex items-center justify-between gap-4">
                                    <label class="inline-flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-zinc-300 text-brand-600 focus:ring-brand-500" />
                                        {{ __('app.member.auth.remember_me') }}
                                    </label>
                                </div>

                                <x-ui.button type="submit" variant="primary" size="md" class="w-full">
                                    {{ __('app.member_portal.login_button') }}
                                </x-ui.button>

                                <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('app.member.auth.no_account') }}
                                    <button type="button" @click="mode = 'register'" class="font-medium text-brand-500 hover:text-brand-400">
                                        {{ __('app.member.auth.register') }}
                                    </button>
                                </p>
                            </form>
                        </div>

                        <div x-show="mode === 'register'" x-cloak style="display: {{ $initialMode === 'register' ? 'block' : 'none' }};">
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

                                <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('app.member.auth.already_registered') }}
                                    <button type="button" @click="mode = 'login'" class="font-medium text-brand-500 hover:text-brand-400">
                                        {{ __('app.member.auth.sign_in') }}
                                    </button>
                                </p>
                            </form>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
</x-layouts.minimal>
