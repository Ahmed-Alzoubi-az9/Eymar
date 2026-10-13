<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioItem extends Model
{
    protected $fillable = ['provider_id', 'title', 'description'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioItemImage::class)->orderBy('sort_order');
    }
}
