<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceQuote extends Model
{
    protected $fillable = [
        'service_request_id', 'provider_id', 'message', 'amount', 'estimated_days', 'status',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
