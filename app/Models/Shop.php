<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use AliBayat\LaravelCategorizable\Categorizable;
use AliBayat\LaravelCategorizable\Category;

class Shop extends Model
{
    use Categorizable;
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'image',
        'printer_ip'
        // 'status'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_shop')->withTimestamps();
    }

    public function getProductsByCategory()
    {
        $category_ids = $this->categoriesIds();
        $products = Category::whereIn('id', $category_ids)->get()->map(function ($cat) {
            return $cat->allEntries(Product::class)->get();
        })->flatten();
        return $products;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_shop')->where('type', 'cashier')->withTimestamps();
    }
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    // public function items()
    // {
    //     return $this->hasManyThrough(Order::class, OrderItem::class);
    // }
    // public function categories()
    // {
    //     return $this->belongsToMany(Category::class, 'shop_categories')->withTimestamps();
    // }
}
