<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'percentage', 'method', 'amount'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_products');
    }
}
