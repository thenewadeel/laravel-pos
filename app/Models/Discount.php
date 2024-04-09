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
        return $this->belongsToMany(Product::class, 'discount_product')
            ->withTimestamps()
            ->withPivot('name');
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'discount_order')
            ->withTimestamps()
            ->withPivot('name');
    }
}
