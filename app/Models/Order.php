<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use LogsActivity;
    protected static $recordEvents = ['updated', 'deleted'];
    protected $fillable = [
        // Unique identifier for the order in the POS system
        'POS_number',
        // The ID of the customer who placed the order
        'customer_id',
        // The ID of the shop where the order was placed
        'shop_id',
        // The ID of the user who placed the order
        'user_id',
        // The state of the order. Possible values: 'preparing', 'served', 'wastage', 'closed'
        // Possible states:
        // - 'preparing': The order is being prepared by the staff
        // - 'served': The order is ready to be delivered to the customer
        // - 'wastage': The order is not collected by the customer
        // - 'closed': The order is already delivered to the customer
        'state',
        // The type of order. Possible values: 'dine-in', 'take-away', 'delivery'
        // Possible types:
        // - 'dine-in': The order is placed at the shop's counter
        // - 'take-away': The order is collected by the customer outside the shop
        // - 'delivery': The order is delivered to the customer at their specified location
        'type',
        // The table number where the order is placed
        'table_number',
        // The name of the waiter who is assigned to this order
        'waiter_name',
        // Notes about the order
        'notes'
    ];
    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        // 'user_id' => auth()->id(),
        'customer_id' => '122',
        // 'shop_id' => '',
        'table_number' => '1',
        'state' => 'preparing',
        'type' => 'dine-in',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('orders')
            ->logFillable()
            ->logOnlyDirty();
        // ->logOnly(['name', 'text']);
        // Chain fluent methods for configuration options
    }
    protected static function boot()
    {
        parent::boot();

        // static::created();
    }
    public function assignPOS()
    {
        $this->POS_number = sprintf('%04d', (int)Order::where('POS_number', '!=', 'null')->where('created_at', '>=', $this->created_at->startOfMonth())->count() + 1) . '-' . $this->created_at->format('d-m-Y');
        $this->save();
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function products()
    {
        return $this->hasManyThrough(Product::class, OrderItem::class, 'order_id', 'id', 'id', 'product_id');
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
        return __('user.unknown');
    }

    public function total()
    {
        return $this->items->map(function ($i) {
            return $i->price;
        })->sum();
    }
    public function discountedTotal() //final amount after discounting
    {
        $totalPrice = $this->total();
        foreach ($this->discounts as $discount) {
            $totalPrice = $discount->apply($totalPrice);
            // $totalPrice -= $totalPrice * ($discount->percentage / 100);
        }
        return $totalPrice;
        // $totalPrice = $this->total();
        // foreach ($this->discounts as $discount) {
        //     $totalPrice -= $totalPrice * ($discount->percentage / 100);
        // }
        // return $totalPrice;
    }
    public function discountAmount() //total amount to be discounted
    {
        return $this->total() - $this->discountedTotal();
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

    public function stateLabel()
    {
        if ($this->receivedAmount() == 0) {
            return __('order.Not_Paid');
        } elseif ($this->receivedAmount() < $this->discountedTotal()) {
            return __('order.Partial');
        } elseif ($this->receivedAmount() == $this->discountedTotal()) {
            return   __('order.Paid');
        } elseif ($this->receivedAmount() > $this->discountedTotal()) {
            return  __('order.Change');
        }
    }
    public function hasFeedback()
    {
        return Feedback::where('order_id', $this->id)->exists();
    }
    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }
}
