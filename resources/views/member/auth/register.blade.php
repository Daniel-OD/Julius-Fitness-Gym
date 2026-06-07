<x-layouts.minimal :title="__('app.member.auth.register')">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
        {{ __('app.member.auth.register') }}
    </h1>
    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
        {{ __('app.member.auth.register_subtitle') }}
    </p>

    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-400">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('member.register') }}" class="mt-7 space-y-5">
        @csrf

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('app.fields.name') }}
            </label>
            <input id="name" name="name" type="text" value="{{ old('name') }}"
                required autofocus autocomplete="name"
                class="block w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400">
        </div>

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('app.fields.email') }}
            </label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                required autocomplete="username"
                class="block w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400">
        </div>

        <div>
            <label for="contact" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('app.fields.contact') }}
                <span class="ml-1 text-xs text-zinc-400">({{ __('app.optional') }})</span>
            </label>
            <input id="contact" name="contact" type="tel" value="{{ old('contact') }}"
                autocomplete="tel"
                class="block w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400">
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('app.fields.password') }}
            </label>
            <input id="password" name="password" type="password"
                required autocomplete="new-password"
                class="block w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400">
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ __('app.fields.confirm_password') }}
            </label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                required autocomplete="new-password"
                class="block w-full rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400">
        </div>

        <x-ui.button type="submit" variant="primary" size="md" class="w-full">
            {{ __('app.member.auth.create_account') }}
        </x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ __('app.member.auth.already_registered') }}
        <a href="{{ route('member.login') }}" class="font-medium text-brand-500 hover:text-brand-400">
            {{ __('app.member.auth.sign_in') }}
        </a>
    </p>
</x-layouts.minimal>
