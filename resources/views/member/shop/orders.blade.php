@extends('member.layouts.app')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('app.shop.purchase_history') }}</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('app.shop.purchase_history_subtitle') }}</p>
        </div>
        <a href="{{ route('member.shop.index') }}"
            class="rounded-xl border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-white/15 dark:text-zinc-200 dark:hover:bg-white/5">
            {{ __('app.shop.back_to_shop') }}
        </a>
    </div>

    @if ($sales->isEmpty())
        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('app.shop.no_orders') }}</p>
    @else
        <div class="space-y-4">
            @foreach ($sales as $sale)
                <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-950">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold">#{{ $sale->id }}</p>
                            <p class="text-sm text-zinc-500">{{ $sale->created_at?->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold">{{ \App\Helpers\Helpers::formatCurrency((float) $sale->total) }}</p>
                            <p class="text-xs uppercase tracking-wider text-zinc-500">{{ $sale->status?->getLabel() }}</p>
                        </div>
                    </div>
                    <ul class="mt-4 divide-y divide-zinc-100 text-sm dark:divide-white/5">
                        @foreach ($sale->items as $item)
                            <li class="flex justify-between gap-4 py-2">
                                <span>{{ $item->product?->name }} × {{ $item->quantity }}</span>
                                <span>{{ \App\Helpers\Helpers::formatCurrency((float) $item->subtotal) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <div class="mt-6">{{ $sales->links() }}</div>
    @endif
@endsection
