<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class OrderItem extends Model
{
    use LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('orders-items')
            ->logFillable();
        // ->logOnlyDirty();
        // ->logOnly(['name', 'text']);
        // Chain fluent methods for configuration options
    }
    protected $fillable = [
        'price',
        'quantity',
        'product_id',
        'order_id',
        'product_name',
        'product_rate',
        'unit_price',
        'total_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function shop()
    {
        return $this->hasOneThrough(Shop::class, Order::class);
    }
    public function isBurnt()
    {
        return ($this->product_name && $this->product_rate && $this->product_id == null);
    }
    public function bakeItem()
    {
        if ($this->product->id) {
            // Log::info("OrderItem with ID # " . $this->id . " is being baked");
            $this->product_name = $this->product->name;
            $this->product_rate = $this->product->price;
            $this->product_id = null;

            $this->save();
            // Log::info("OrderItem " . $this . " is baked");
        }
    }
}
