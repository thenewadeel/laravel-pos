<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'sort_order',
        'is_active',
        'layout_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'layout_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function tables()
    {
        return $this->hasMany(RestaurantTable::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function getTableCount()
    {
        return $this->tables()->count();
    }

    public function getAvailableTableCount()
    {
        return $this->tables()->where('status', 'available')->count();
    }
}
