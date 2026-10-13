<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    protected $fillable = [
        'customer_id', 'category_id', 'title', 'description', 'location',
        'budget_min', 'budget_max', 'status', 'accepted_provider_id',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function acceptedProvider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_provider_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(ServiceQuote::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
