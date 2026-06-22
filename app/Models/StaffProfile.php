<?php

namespace App\Models;

use App\Enums\SalaryType;
use App\Models\Concerns\CascadesSoftDeletes;
use Database\Factories\StaffProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'department',
    'position',
    'hire_date',
    'base_salary',
    'salary_type',
    'bank_details',
    'emergency_contact',
    'notes',
])]
class StaffProfile extends Model
{
    /** @use HasFactory<StaffProfileFactory> */
    use CascadesSoftDeletes, HasFactory, SoftDeletes;

    /**
     * @return list<string>
     */
    protected static function relationsToCascade(): array
    {
        return ['shiftAssignments', 'attendances', 'leaves', 'payrollItems'];
    }

    protected $casts = [
        'hire_date' => 'date',
        'base_salary' => 'decimal:2',
        'salary_type' => SalaryType::class,
        'bank_details' => 'encrypted:array',
    ];

    protected static function booted(): void
    {
        static::creating(function (StaffProfile $profile): void {
            if (blank($profile->employee_code)) {
                $profile->employee_code = static::nextEmployeeCode();
            }

            if (blank($profile->attendance_token)) {
                $profile->attendance_token = Str::random(32);
            }
        });
    }

    public static function nextEmployeeCode(): string
    {
        $latest = static::query()
            ->where('employee_code', 'like', 'EMP-%')
            ->orderByDesc('id')
            ->value('employee_code');

        $number = 1;

        if (is_string($latest) && preg_match('/^EMP-(\d+)$/', $latest, $matches)) {
            $number = ((int) $matches[1]) + 1;
        }

        return sprintf('EMP-%03d', $number);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ShiftAssignment, $this>
     */
    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class, 'user_id', 'user_id');
    }

    /**
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }

    /**
     * @return HasMany<Leave, $this>
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'user_id', 'user_id');
    }

    /**
     * @return HasMany<PayrollItem, $this>
     */
    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'user_id', 'user_id');
    }

    public function ensureAttendanceToken(): void
    {
        if (filled($this->attendance_token)) {
            return;
        }

        $this->forceFill(['attendance_token' => Str::random(32)])->save();
    }
}
