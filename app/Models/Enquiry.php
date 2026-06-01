<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $email
 * @property string|null $contact
 * @property Carbon|null $date
 * @property string|null $gender
 * @property Carbon|null $dob
 * @property Status|null $status
 * @property array<int, mixed>|null $interested_in
 * @property string|null $source
 * @property string|null $goal
 * @property Carbon|null $start_by
 */
class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'contact',
        'date',
        'gender',
        'dob',
        'status',
        'address',
        'country',
        'city',
        'state',
        'pincode',
        'interested_in',
        'source',
        'goal',
        'start_by',
    ];

    protected $casts = [
        'interested_in' => 'array',
        'date' => 'date',
        'dob' => 'date',
        'start_by' => 'date',
        'status' => Status::class,
    ];

    /**
     * @return HasMany<FollowUp, $this>
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['followUps'];
    }
}
