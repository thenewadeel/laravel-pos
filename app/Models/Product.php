<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'barcode',
        'price',
        'quantity',
        'status',
        'category'
    ];

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    public function categories()
    {
        return $this->hasMany(CategoryProducts::class);
    }
    public function categoryNames()
    {
        return $this->hasManyThrough(Category::class, CategoryProducts::class, 'product_id', 'id', 'id', 'category_id');
    }
}
