<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'description',
])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /**
     * @return HasMany<Plan, $this>
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['plans'];
    }
}
