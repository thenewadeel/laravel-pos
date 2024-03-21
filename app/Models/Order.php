<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'shop_id',
        'user_id',
        'state',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_orders', 'order_id', 'discount_id');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function getCustomerName()
    {
        // Log::info($this->customer);
        if ($this->customer) {
            return $this->customer->name;
        }
        return __('customer.working');
    }
    public function getUserName()
    {
        // Log::info($this->user);
        if ($this->user) {
            return $this->user->first_name;
        }
        return __('customer.working');
    }

    public function total()
    {
        return $this->items->map(function ($i) {
            return $i->price;
        })->sum();
    }
    public function discountedTotal()
    {
        $totalPrice = $this->total();
        foreach ($this->discounts as $discount) {
            $totalPrice -= $totalPrice * ($discount->percentage / 100);
        }
        return $totalPrice;
    }
    public function discountAmount()
    {
        $totalPrice = $this->total();
        foreach ($this->discounts as $discount) {
            $totalPrice -= $totalPrice * ($discount->percentage / 100);
        }
        return $this->total() - $totalPrice;
    }
    public function formattedDiscountedTotal()
    {
        return number_format($this->discountedTotal(), 2);
    }
    public function formattedTotal()
    {
        return number_format($this->total(), 2);
    }

    public function receivedAmount()
    {
        return $this->payments->map(function ($i) {
            return $i->amount;
        })->sum();
    }

    public function formattedReceivedAmount()
    {
        return number_format($this->receivedAmount(), 2);
    }

    public function balance()
    {
        return $this->discountedTotal() - $this->receivedAmount();
    }

    public function formattedBalance()
    {
        return number_format($this->balance(), 2);
    }
}
