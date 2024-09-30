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
        // $categories = Category::query();
        $category_ids = $this->categoriesIds();

        // $categories = $categories->where('type', 'product');
        // $categories = $categories->whereIn('id', $category_ids);
        // Product::query('category_id', $category_ids);

        $products =  Category::whereIn('id', $category_ids)->get()->map(function ($cat) {
            return $cat->allEntries(Product::class)->get();
        })->flatten();

        return $products; //= Category::whereIn('id', Shop::first()->categoriesIds())->get();
        // return $this->hasMany(Product::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_shop')->withTimestamps();
    }
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
    // public function categories()
    // {
    //     return $this->belongsToMany(Category::class, 'shop_categories')->withTimestamps();
    // }
}
