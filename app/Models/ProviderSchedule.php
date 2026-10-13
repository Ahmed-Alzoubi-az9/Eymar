<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSchedule extends Model
{
    protected $fillable = ['provider_id', 'date', 'start_time', 'end_time', 'is_available'];

    protected function casts(): array
    {
        return ['date' => 'date', 'is_available' => 'boolean'];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
