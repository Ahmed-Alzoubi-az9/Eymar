<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioItemImage extends Model
{
    protected $fillable = ['portfolio_item_id', 'image_path', 'sort_order'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PortfolioItem::class, 'portfolio_item_id');
    }
}
