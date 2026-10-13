<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['type', 'name', 'name_ar', 'slug'];

    public function getDisplayNameAttribute(): string
    {
        return app()->isLocale('ar') && $this->name_ar
            ? $this->name_ar
            : $this->name;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
