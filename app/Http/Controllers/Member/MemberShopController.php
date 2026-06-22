<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Services\Shop\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberShopController extends Controller
{
    public function __construct(private readonly SaleService $saleService) {}

    public function index(): View
    {
        $products = Product::query()
            ->with('stockLevel')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $cart = session('member_shop_cart', []);

        return view('member.shop.index', compact('products', 'cart'));
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = session('member_shop_cart', []);
        $productId = (int) $validated['product_id'];
        $cart[$productId] = ($cart[$productId] ?? 0) + (int) $validated['quantity'];
        session(['member_shop_cart' => $cart]);

        return redirect()->route('member.shop.index')->with('success', __('app.shop.added_to_cart'));
    }

    public function removeFromCart(int $productId): RedirectResponse
    {
        $cart = session('member_shop_cart', []);
        unset($cart[$productId]);
        session(['member_shop_cart' => $cart]);

        return redirect()->route('member.shop.index');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cart = session('member_shop_cart', []);

        if ($cart === []) {
            return redirect()->route('member.shop.index')->with('error', __('app.shop.errors.no_items'));
        }

        $items = collect($cart)->map(fn (int $quantity, int $productId): array => [
            'product_id' => $productId,
            'quantity' => $quantity,
        ])->values()->all();

        try {
            $this->saleService->create([
                'member_id' => auth('member')->id(),
                'payment_method' => $request->input('payment_method', 'online'),
                'items' => $items,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return redirect()->route('member.shop.index')->with('error', $exception->getMessage());
        }

        session()->forget('member_shop_cart');

        return redirect()->route('member.shop.orders')->with('success', __('app.shop.order_placed'));
    }

    public function orders(): View
    {
        $sales = Sale::query()
            ->with('items.product')
            ->where('member_id', auth('member')->id())
            ->latest()
            ->paginate(10);

        return view('member.shop.orders', compact('sales'));
    }
}
