<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use AliBayat\LaravelCategorizable\Categorizable;

class Product extends Model
{
    use Categorizable;
    protected $fillable = [
        'name',
        'description',
        'image',
        // 'barcode',
        'price',
        'quantity',
        'status',
        'kitchen_printer_ip',
        // 'category'
    ];

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }


    // public function categories()
    // {
    //     return $this->hasManyThrough(Category::class, CategoryProducts::class, 'product_id', 'id', 'id', 'category_id');
    // }
    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_product')
            ->withTimestamps()
            ->withPivot('name');
    }
    public function orders()
    {
        return $this->hasManyThrough(Order::class, OrderItem::class, 'product_id', 'id', 'id', 'order_id');
    }
}
