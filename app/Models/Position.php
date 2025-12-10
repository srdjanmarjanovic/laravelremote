<?php

namespace App\Models;

use App\Enums\ListingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'long_description',
        'company_id',
        'created_by_user_id',
        'seniority',
        'salary_min',
        'salary_max',
        'remote_type',
        'location_restriction',
        'status',
        'listing_type',
        'is_external',
        'external_apply_url',
        'allow_platform_applications',
        'expires_at',
        'published_at',
        'paid_at',
        'payment_id',
    ];

    protected function casts(): array
    {
        return [
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'listing_type' => ListingType::class,
            'is_external' => 'boolean',
            'allow_platform_applications' => 'boolean',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function customQuestions(): HasMany
    {
        return $this->hasMany(CustomQuestion::class)->orderBy('order');
    }

    public function views(): HasMany
    {
        return $this->hasMany(PositionView::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    public function canReceiveApplications(): bool
    {
        return $this->isPublished() &&
               ! $this->isExpired() &&
               $this->allow_platform_applications &&
               ! $this->is_external;
    }

    public function hasPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function getLatestPayment(): ?Payment
    {
        return $this->payments()
            ->where('status', \App\Enums\PaymentStatus::Completed)
            ->latest()
            ->first();
    }
}
