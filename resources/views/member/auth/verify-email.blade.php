<x-layouts.minimal :title="__('app.member.auth.verify_email')">
    <div class="flex flex-col items-center text-center">
        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-500/10 text-brand-500">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
        </span>

        <h1 class="mt-5 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
            {{ __('app.member.auth.verify_email') }}
        </h1>
        <p class="mt-3 max-w-sm text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
            {{ __('app.member.auth.verify_email_sent_to', ['email' => auth('member')->user()->email]) }}
        </p>

        @if (session('status') === 'verification-link-sent')
            <div class="mt-5 w-full rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-400">
                {{ __('app.member.auth.verification_sent') }}
            </div>
        @endif

        <div class="mt-8 flex w-full flex-col gap-3">
            <form method="POST" action="{{ route('member.verification.resend') }}">
                @csrf
                <x-ui.button type="submit" variant="primary" size="md" class="w-full">
                    {{ __('app.member.auth.resend_verification') }}
                </x-ui.button>
            </form>

            <form method="POST" action="{{ route('member.logout') }}">
                @csrf
                <x-ui.button type="submit" variant="ghost" size="md" class="w-full">
                    {{ __('app.member.auth.logout') }}
                </x-ui.button>
            </form>
        </div>
    </div>
</x-layouts.minimal>
