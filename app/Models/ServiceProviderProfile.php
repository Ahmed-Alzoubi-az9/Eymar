<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProviderProfile extends Model
{
    protected $fillable = [
        'provider_type',
        'experience_years',
        'previous_work',
        'pricing_basis',
        'service_rate',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'service_rate' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
