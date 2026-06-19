<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'amount',
    'date',
    'due_date',
    'paid_at',
    'category',
    'status',
    'vendor',
    'notes',
])]
class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'status' => Status::class,
    ];
}
