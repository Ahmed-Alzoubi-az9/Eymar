<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'seller_id', 'category_id', 'name', 'slug', 'description',
        'price', 'stock', 'image_path', 'is_active', 'product_type',
        'manufacturing_company', 'manufacturing_country', 'production_date',
        'expiry_date', 'storage_info', 'risk_info', 'usage_info',
        'driver_name', 'driver_phone',
        'is_rentable', 'rental_rate_per_day',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'production_date' => 'date',
            'expiry_date' => 'date',
            'is_rentable' => 'boolean',
            'rental_rate_per_day' => 'decimal:2',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
