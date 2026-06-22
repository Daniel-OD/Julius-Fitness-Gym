@extends('member.layouts.app')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('app.shop.member_shop_title') }}</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('app.shop.member_shop_subtitle') }}</p>
        </div>
        <a href="{{ route('member.shop.orders') }}"
            class="rounded-xl border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-white/15 dark:text-zinc-200 dark:hover:bg-white/5">
            {{ __('app.shop.purchase_history') }}
        </a>
    </div>

    @if ($cart !== [])
        <section class="mb-8 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-950">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('app.shop.cart') }}</h2>
            <ul class="mt-4 divide-y divide-zinc-100 dark:divide-white/5">
                @foreach ($cart as $productId => $quantity)
                    @php $product = $products->firstWhere('id', $productId); @endphp
                    @if ($product)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <div>
                                <p class="font-medium">{{ $product->name }}</p>
                                <p class="text-sm text-zinc-500">{{ $quantity }} × {{ \App\Helpers\Helpers::formatCurrency((float) $product->price) }}</p>
                            </div>
                            <form method="POST" action="{{ route('member.shop.cart.remove', $productId) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('app.actions.remove') }}</button>
                            </form>
                        </li>
                    @endif
                @endforeach
            </ul>
            <form method="POST" action="{{ route('member.shop.checkout') }}" class="mt-4">
                @csrf
                <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200">
                    {{ __('app.shop.checkout') }}
                </button>
            </form>
        </section>
    @endif

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($products as $product)
            @php
                $inStock = ! $product->track_stock || $product->currentStock() > 0;
                $stockLabel = $product->track_stock
                    ? __('app.shop.stock_available', ['count' => $product->currentStock()])
                    : __('app.shop.always_available');
            @endphp
            <article class="flex flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-950">
                <h3 class="text-lg font-semibold">{{ $product->name }}</h3>
                @if ($product->description)
                    <p class="mt-2 flex-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $product->description }}</p>
                @endif
                <p class="mt-4 text-xl font-bold">{{ \App\Helpers\Helpers::formatCurrency((float) $product->price) }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $stockLabel }}</p>
                @if ($inStock)
                    <form method="POST" action="{{ route('member.shop.cart.add') }}" class="mt-4 flex items-end gap-2">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <label class="flex-1 text-xs text-zinc-500">
                            {{ __('app.shop.quantity') }}
                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->track_stock ? $product->currentStock() : 99 }}"
                                class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-zinc-900">
                        </label>
                        <button type="submit"
                            class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600">
                            {{ __('app.shop.add_to_cart') }}
                        </button>
                    </form>
                @else
                    <p class="mt-4 text-sm font-medium text-red-600">{{ __('app.shop.out_of_stock') }}</p>
                @endif
            </article>
        @empty
            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('app.shop.no_products') }}</p>
        @endforelse
    </div>
@endsection
