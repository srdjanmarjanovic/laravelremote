<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function developerProfile(): HasOne
    {
        return $this->hasOne(DeveloperProfile::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('role', 'invited_by', 'joined_at')
            ->withTimestamps();
    }

    public function createdCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'created_by_user_id');
    }

    public function createdPositions(): HasMany
    {
        return $this->hasMany(Position::class, 'created_by_user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    public function isHR(): bool
    {
        return $this->role === 'hr' || $this->companies()->exists();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasCompleteProfile(): bool
    {
        return $this->developerProfile && $this->developerProfile->isComplete();
    }

    public function canAccessCompany(Company $company): bool
    {
        return $this->isAdmin() ||
               $this->companies()->where('company_id', $company->id)->exists();
    }

    public function canManagePosition(Position $position): bool
    {
        return $this->isAdmin() ||
               $position->created_by_user_id === $this->id ||
               $this->canAccessCompany($position->company);
    }
}
