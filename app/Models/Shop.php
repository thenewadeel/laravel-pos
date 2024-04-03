<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
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
        return $this->hasMany(Product::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_shop')->withTimestamps();
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'shop_categories')->withTimestamps();
    }
}
