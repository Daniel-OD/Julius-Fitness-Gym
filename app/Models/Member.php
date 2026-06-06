<?php

namespace App\Models;

use App\Enums\Status;
use App\Helpers\Helpers;
use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string|null $photo
 * @property string $code
 * @property string|null $checkin_token
 * @property string|null $name
 * @property string|null $email
 * @property string|null $contact
 * @property string|null $emergency_contact
 * @property string|null $health_issue
 * @property string|null $gender
 * @property Carbon|null $dob
 * @property string|null $address
 * @property string|null $country
 * @property string|null $state
 * @property string|null $city
 * @property string|null $pincode
 * @property string|null $source
 * @property string|null $goal
 * @property Status|null $status
 * @property int|null $user_id
 */
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'photo',
        'name',
        'email',
        'contact',
        'emergency_contact',
        'health_issue',
        'gender',
        'dob',
        'address',
        'country',
        'state',
        'city',
        'pincode',
        'source',
        'goal',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
        'status' => Status::class,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ensureCheckinToken(): void
    {
        if ($this->checkin_token) {
            return;
        }

        $this->checkin_token = Str::random(32);
        $this->save();
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasMany<CheckIn, $this>
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $member): void {
            if (! $member->code) {
                $member->code = Helpers::generateLastNumber('member', Member::class, null, 'code');
            }
            Helpers::updateLastNumber('member', $member->code);

            if (! $member->checkin_token) {
                $member->checkin_token = Str::random(32);
            }
        });

        static::created(function (): void {
            $backup = is_array($b = Helpers::getSettings()['backup'] ?? null) ? $b : [];

            if (! empty($backup['enabled']) && in_array($backup['trigger'] ?? '', ['after_member', 'both'], true)) {
                Artisan::call('app:backup', ['--trigger' => 'after_member']);
            }
        });
    }

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['subscriptions'];
    }
}
