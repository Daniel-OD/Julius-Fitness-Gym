<?php

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function cashierWithCreateSale(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate([
        'name' => 'Create:Sale',
        'guard_name' => 'web',
    ]));

    return $user;
}

it('returns 422 (not 500) when a sale exceeds available stock', function (): void {
    $product = Product::factory()->create([
        'price' => 25.0,
        'track_stock' => true,
        'is_active' => true,
    ]);
    StockLevel::query()->updateOrCreate(['product_id' => $product->id], ['quantity' => 1]);

    Sanctum::actingAs(cashierWithCreateSale());

    $this->postJson('/api/v1/sales', [
        'payment_method' => 'cash',
        'items' => [
            ['product_id' => $product->id, 'quantity' => 5],
        ],
    ])->assertStatus(422)->assertJsonValidationErrors('items');

    expect($product->fresh()->currentStock())->toBe(1);
});
