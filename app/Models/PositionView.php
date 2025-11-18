<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionView extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_id',
        'ip_address_hash',
        'country_code',
        'city',
        'user_agent',
        'device_type',
        'device_name',
        'browser',
        'os',
        'referrer',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
