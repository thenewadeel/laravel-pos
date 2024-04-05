<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image'];
    public function products()
    {
        return $this->hasManyThrough(Product::class, CategoryProducts::class, 'category_id', 'id', 'id', 'product_id');
    }
}
