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
    <x-layouts.partials.head-meta :title="$gymName" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @yield('head')
</head>

<body class="jf-min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-black dark:text-white">
    <header class="border-b border-zinc-200 bg-white/80 backdrop-blur dark:border-white/10 dark:bg-zinc-950/80">
        <div class="jf-safe-x mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="{{ route('member.dashboard') }}" class="flex min-w-0 items-center gap-3">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $gymName }}" class="h-9 w-9 shrink-0 rounded-lg object-cover" />
                @endif
                <span class="truncate text-sm font-semibold tracking-tight">{{ $gymName }}</span>
            </a>

            @auth('member')
                <div class="flex items-center gap-2">
                    <a href="{{ route('member.password.edit') }}"
                        class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-100 dark:border-white/15 dark:text-zinc-300 dark:hover:bg-white/5">
                        {{ __('app.member_portal.change_password_link') }}
                    </a>
                    <form method="POST" action="{{ route('member.logout') }}">
                        @csrf
                        <button type="submit"
                            class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-100 dark:border-white/15 dark:text-zinc-300 dark:hover:bg-white/5">
                            {{ __('app.actions.logout') }}
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="jf-safe-x jf-safe-b mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
        @yield('content')
    </main>
</body>

</html>
