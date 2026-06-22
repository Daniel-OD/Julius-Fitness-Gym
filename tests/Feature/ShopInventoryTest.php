<?php

use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Models\Member;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Services\Shop\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeProductWithStock(int $quantity = 10, float $price = 25.0): Product
{
    $product = Product::factory()->create([
        'price' => $price,
        'track_stock' => true,
        'is_active' => true,
    ]);

    StockLevel::query()->updateOrCreate(
        ['product_id' => $product->id],
        ['quantity' => $quantity],
    );

    return $product->fresh(['stockLevel']);
}

it('records stock movement and updates stock level on stock in', function (): void {
    $product = makeProductWithStock(5);

    StockMovement::query()->create([
        'product_id' => $product->id,
        'type' => StockMovementType::In,
        'quantity' => 3,
    ]);

    expect($product->fresh()->currentStock())->toBe(8)
        ->and(StockMovement::query()->count())->toBe(1);
});

it('prevents stock from going negative on stock out', function (): void {
    $product = makeProductWithStock(2);

    expect(fn () => StockMovement::query()->create([
        'product_id' => $product->id,
        'type' => StockMovementType::Out,
        'quantity' => 5,
    ]))->toThrow(InvalidArgumentException::class);

    expect($product->fresh()->currentStock())->toBe(2);
});

it('completes a sale and deducts stock for tracked products', function (): void {
    $product = makeProductWithStock(10, 20.0);
    $member = Member::factory()->create();

    $sale = app(SaleService::class)->create([
        'member_id' => $member->id,
        'payment_method' => 'cash',
        'items' => [
            ['product_id' => $product->id, 'quantity' => 3],
        ],
    ]);

    expect($sale->status)->toBe(SaleStatus::Completed)
        ->and((float) $sale->total)->toBe(60.0)
        ->and($product->fresh()->currentStock())->toBe(7)
        ->and($sale->items)->toHaveCount(1)
        ->and(StockMovement::query()->where('type', StockMovementType::Out)->count())->toBe(1);
});

it('rejects a sale when stock is insufficient', function (): void {
    $product = makeProductWithStock(1);

    expect(fn () => app(SaleService::class)->create([
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ]))->toThrow(InvalidArgumentException::class);

    expect($product->fresh()->currentStock())->toBe(1);
});

it('restores stock when a completed sale is cancelled', function (): void {
    $product = makeProductWithStock(8);

    $sale = app(SaleService::class)->create([
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ]);

    expect($product->fresh()->currentStock())->toBe(6);

    app(SaleService::class)->cancel($sale);

    expect($sale->fresh()->status)->toBe(SaleStatus::Cancelled)
        ->and($product->fresh()->currentStock())->toBe(8);
});

it('skips stock deduction for products that do not track stock', function (): void {
    $product = Product::factory()->withoutStockTracking()->create([
        'price' => 15.0,
        'is_active' => true,
    ]);

    $sale = app(SaleService::class)->create([
        'items' => [
            ['product_id' => $product->id, 'quantity' => 5],
        ],
    ]);

    expect($sale->status)->toBe(SaleStatus::Completed)
        ->and(StockMovement::query()->count())->toBe(0);
});

it('member can view shop page when authenticated', function (): void {
    $member = Member::factory()->create(['email_verified_at' => now()]);
    Product::factory()->create(['is_active' => true]);

    $this->actingAs($member, 'member')
        ->get(route('member.shop.index'))
        ->assertSuccessful()
        ->assertSee(__('app.shop.member_shop_title'));
});

it('member can checkout and view order history', function (): void {
    $member = Member::factory()->create(['email_verified_at' => now()]);
    $product = makeProductWithStock(5, 10.0);

    $this->actingAs($member, 'member')
        ->post(route('member.shop.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])
        ->assertRedirect(route('member.shop.index'));

    $this->post(route('member.shop.checkout'))
        ->assertRedirect(route('member.shop.orders'));

    expect($member->sales()->count())->toBe(1)
        ->and($product->fresh()->currentStock())->toBe(3);

    $this->get(route('member.shop.orders'))
        ->assertSuccessful()
        ->assertSee(__('app.shop.purchase_history'));
});
