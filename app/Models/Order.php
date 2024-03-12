<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'shop_id',
        'user_id'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
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
        return $this->total() - $this->receivedAmount();
    }

    public function formattedBalance()
    {
        return number_format($this->balance(), 2);
    }
}
