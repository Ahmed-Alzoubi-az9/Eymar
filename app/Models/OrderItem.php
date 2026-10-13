<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'fulfillment_type',
        'rental_days',
        'rental_start_date',
        'return_date',
    ];

    protected function casts(): array
    {
        return [
            'rental_days' => 'integer',
            'unit_price' => 'decimal:2',
            'rental_start_date' => 'date',
            'return_date' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
