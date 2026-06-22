<?php

namespace App\Models;

use Database\Factories\FoodItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'brand',
    'calories_per_100g',
    'protein',
    'carbs',
    'fat',
    'fiber',
    'serving_size',
    'serving_unit',
    'barcode',
    'is_verified',
])]
class FoodItem extends Model
{
    /** @use HasFactory<FoodItemFactory> */
    use HasFactory;

    protected $casts = [
        'calories_per_100g' => 'decimal:2',
        'protein' => 'decimal:2',
        'carbs' => 'decimal:2',
        'fat' => 'decimal:2',
        'fiber' => 'decimal:2',
        'serving_size' => 'decimal:2',
        'is_verified' => 'boolean',
    ];
}
