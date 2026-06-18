<x-layouts.minimal :title="__('app.member_portal.forgot_password_title')">
    <div class="mx-auto w-full max-w-md">
        <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-zinc-950 sm:p-8">
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-zinc-500 dark:text-zinc-400">Julius Fitness</p>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    {{ __('app.member_portal.forgot_password_title') }}
                </h1>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('app.member_portal.forgot_password_intro') }}
                </p>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800/30 dark:bg-emerald-950/50 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('member.password.email') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('app.fields.email') }}
                    </label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="block w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100 dark:border-white/10 dark:bg-zinc-950 dark:text-white dark:focus:border-brand-400 dark:focus:ring-brand-400" />
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.button type="submit" variant="primary" size="md" class="w-full">
                    {{ __('app.member_portal.forgot_password_button') }}
                </x-ui.button>
            </form>

            <p class="mt-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('member.login') }}" class="font-medium text-zinc-900 hover:underline dark:text-white">
                    {{ __('app.member_portal.back_to_login') }}
                </a>
            </p>
        </div>
    </div>
</x-layouts.minimal>
