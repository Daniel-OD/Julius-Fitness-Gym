@php
    use App\Helpers\Helpers;
    use Illuminate\Support\Facades\Storage;

    $settings = Helpers::getSettings();
    $gymName = data_get($settings, 'general.gym_name') ?: config('app.name');
    $logoPath = data_get($settings, 'general.gym_logo');

    if (is_array($logoPath)) {
        $logoPath = $logoPath[0] ?? null;
    }

    $logoUrl = is_string($logoPath) && filled($logoPath)
        ? Storage::disk('public')->url($logoPath)
        : null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">

<head>
    <x-layouts.partials.head-meta :title="__('app.member_portal.set_password_title', ['gym' => $gymName])" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="jf-min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-black dark:text-white">
    <div class="jf-safe-x jf-safe-b mx-auto flex min-h-full max-w-sm flex-col justify-center px-4 py-10 sm:px-6">
        <div class="mb-8 text-center">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $gymName }}"
                    class="mx-auto mb-4 h-16 w-16 rounded-2xl object-cover shadow-sm" />
            @endif
            <h1 class="text-2xl font-semibold tracking-tight">{{ $gymName }}</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('app.member_portal.set_password_intro') }}
            </p>
        </div>

        @if (session('status'))
            <div
                class="mb-6 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-zinc-950">
            <form method="POST" action="{{ route('member.set-password.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('app.fields.email') }}
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required readonly
                        class="w-full cursor-not-allowed rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 dark:border-white/15 dark:bg-zinc-900/50 dark:text-zinc-300" />
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('app.fields.password') }}
                    </label>
                    <input type="password" id="password" name="password" required autofocus autocomplete="new-password"
                        class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200 dark:border-white/15 dark:bg-zinc-900 dark:text-white dark:focus:border-white/30 dark:focus:ring-white/10" />
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation"
                        class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('app.member_portal.password_confirmation') }}
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition focus:border-zinc-400 focus:ring-2 focus:ring-zinc-200 dark:border-white/15 dark:bg-zinc-900 dark:text-white dark:focus:border-white/30 dark:focus:ring-white/10" />
                </div>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200">
                    {{ __('app.member_portal.set_password_button') }}
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
            <a href="{{ route('member.login') }}" class="font-medium text-zinc-900 hover:underline dark:text-white">
                {{ __('app.member_portal.back_to_login') }}
            </a>
        </p>
    </div>
</body>

</html>
