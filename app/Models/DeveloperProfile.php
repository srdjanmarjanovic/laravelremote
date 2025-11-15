<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'summary',
        'cv_path',
        'profile_photo_path',
        'github_url',
        'linkedin_url',
        'portfolio_url',
        'other_links',
    ];

    protected function casts(): array
    {
        return [
            'other_links' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isComplete(): bool
    {
        return ! empty($this->summary) && ! empty($this->cv_path);
    }
}
