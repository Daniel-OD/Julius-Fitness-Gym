<?php

namespace App\Models;

use App\Enums\Status;
use App\Filament\Auth\Login;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/** @studio Daniel-OD */
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
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
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
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

        return true;
    }

    public function canAccessAnyFilamentPanel(): bool
    {
        if ($this->hasRole('super_admin') || $this->hasRole('owner') || $this->hasRole('employee')) {
            return true;
        }

        return ! $this->roles()->exists();
    }

    public function isEmployeeOnly(): bool
    {
        return $this->hasRole('employee')
            && ! $this->hasRole('owner')
            && ! $this->hasRole('super_admin');
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
