<?php

namespace App\Models;

use App\Enums\Status;
use App\Filament\Auth\Login;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @studio Daniel-OD
 *
 * @property int $id
 * @property string|null $photo
 * @property string $name
 * @property string $email
 * @property string|null $contact
 * @property Carbon|null $dob
 * @property string|null $gender
 * @property string|null $address
 * @property string|null $country
 * @property string|null $city
 * @property string|null $state
 * @property string|null $pincode
 * @property Status|null $status
 * @property bool $must_change_password
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'photo',
    'name',
    'email',
    'password',
    'contact',
    'dob',
    'gender',
    'address',
    'country',
    'city',
    'state',
    'pincode',
    'status',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
            'status' => Status::class,
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * @return HasMany<FollowUp, $this>
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * @return HasMany<Enquiry, $this>
     */
    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    /**
     * @return HasOne<Member, $this>
     */
    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * @return HasOne<StaffProfile, $this>
     */
    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    /**
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * @return HasMany<Leave, $this>
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * @return HasMany<ShiftAssignment, $this>
     */
    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * @return HasMany<PayrollItem, $this>
     */
    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function hasStaffProfile(): bool
    {
        return $this->staffProfile()->exists();
    }

    public function linkedMember(): ?Member
    {
        return $this->member;
    }

    public function hasLinkedMember(): bool
    {
        return $this->member()->exists();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url((string) $this->photo) : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (request()->attributes->get(Login::RELAXED_PANEL_ACCESS_ATTRIBUTE)) {
            return $this->canAccessAnyFilamentPanel();
        }

        if ($this->hasRole('super_admin') || $this->hasRole('owner')) {
            return true;
        }

        if ($this->isEmployeeOnly()) {
            return $panel->getId() === 'office';
        }

        return $this->roles()->exists();
    }

    public function canAccessAnyFilamentPanel(): bool
    {
        return $this->roles()->exists();
    }

    public function isEmployeeOnly(): bool
    {
        return $this->hasRole('employee')
            && ! $this->hasRole('owner')
            && ! $this->hasRole('super_admin');
    }

    public function isInstructor(): bool
    {
        return $this->hasRole('instructor') && ! $this->isAdministrator();
    }

    /**
     * @return BelongsToMany<Member, $this>
     */
    public function assignedMembers(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_instructor_assignments', 'instructor_id', 'member_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<WorkoutTemplate, $this>
     */
    public function workoutTemplates(): HasMany
    {
        return $this->hasMany(WorkoutTemplate::class, 'created_by');
    }

    public function isAdministrator(): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->hasRole('owner');
    }

    public function isClientOnly(): bool
    {
        if ($this->isAdministrator() || $this->isEmployeeOnly()) {
            return false;
        }

        return $this->hasRole('client');
    }

    /**
     * @return list<string>
     */
    public function accessibleDashboards(): array
    {
        if ($this->isAdministrator()) {
            return ['admin', 'office', 'client'];
        }

        if ($this->isEmployeeOnly()) {
            return ['office'];
        }

        if ($this->hasRole('client')) {
            return ['client'];
        }

        return [];
    }

    public function dashboardUrl(string $dashboard): ?string
    {
        if (! in_array($dashboard, $this->accessibleDashboards(), true)) {
            return null;
        }

        return match ($dashboard) {
            'admin' => Filament::getPanel('admin')->getUrl(),
            'office' => Filament::getPanel('office')->getUrl(),
            'client' => route('client.dashboard'),
            default => null,
        };
    }

    public function defaultDashboardUrl(): string
    {
        $dashboards = $this->accessibleDashboards();

        if ($dashboards === []) {
            return route('dashboard');
        }

        return $this->dashboardUrl($dashboards[0]) ?? route('dashboard');
    }

    public function displayRoleName(): string
    {
        if ($this->isAdministrator()) {
            return __('app.roles.administrator');
        }

        if ($this->isEmployeeOnly()) {
            return __('app.roles.employee');
        }

        if ($this->hasRole('client')) {
            return __('app.roles.client');
        }

        $role = $this->roles->first();

        return $role !== null ? (string) $role->getAttributeValue('name') : '';
    }

    /**
     * Panel to open after login. Employees always land on office; managers on the panel they signed into.
     */
    public function postLoginPanelId(?string $loginPanelId = null): string
    {
        if ($this->isEmployeeOnly()) {
            return 'office';
        }

        if ($loginPanelId === 'office') {
            return 'office';
        }

        return 'admin';
    }

    public function postLoginUrl(?string $loginPanelId = null): string
    {
        return Filament::getPanel($this->postLoginPanelId($loginPanelId))->getUrl();
    }
}
