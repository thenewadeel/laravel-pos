<?php

namespace App\Models;

use App\Http\Controllers\OrderHistoryController;
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
        $posNumber = null;
        \DB::transaction(function () {
            // $latestOrder = Order::whereNotNull('POS_number')
            //     ->where('created_at', '>=', $this->created_at->startOfMonth())
            //     ->latest('POS_number')
            //     ->lockForUpdate()
            //     ->first();

            $latestPOSNumber = intval(explode('-', Order::where('created_at', '>=', $this->created_at->startOfMonth())->latest("POS_number")->pluck("POS_number")->first())[0]);

            $posNumber = sprintf('%04d', $latestPOSNumber + 1) . '-' . $this->created_at->format('d-m-Y');
            $this->POS_number = $posNumber;
            $this->save();
        });

        // Create order history
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request = null, orderId: $this->id, actionType: 'pos-assigned', POSNumber: $posNumber);
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
    public function history()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function isBurnt()
    {
        return $this->state == 'closed' && $this->items->every(function ($item) {
            return $item->isBurnt();
        });
    }
    public function scopeBurnt($query)
    {
        return $query->where('state', 'closed')->whereHas('items', function ($query) {
            return $query->where('product_id', null);
        });
    }

    public function scopeNotBurnt($query)
    {
        return $query->where('state', '!=', 'closed')->orWhereHas('items', function ($query) {
            return $query->where('product_id', '!=', null);
        });
    }

    public function bakeOrder()
    {
        if ($this->state == 'closed') {
            if (!$this->isBurnt()) {

                Log::warning("Order with POS # " . $this->POS_number . " is being baked. With " . count($this->items) . " items");
                foreach ($this->items as $item) {
                    $item->bakeItem();
                }
            } else {
                Log::warning("Burnt Order!");
            }
        } else {
            Log::warning("Open Order with POS # " . $this->POS_number . " is being baked!!! ;which can't be right.");
        }
    }
    /*
    -----------------   SCOPES   -----------------
        - start_date
        - end_date
        - date_between
        - order_type >
    - payment_type
        - pos_number (like)
        - order_status [open|closed]
        - customer_ids >
        - customer_name
        - order_takers >
        - shop_ids >
        - cashiers >
        - item_ids >
        - item_name
    */

    public function scopePOS_number($query, $POS_number)
    {
        return $query->where('POS_number', 'LIKE', "%{$POS_number}%");
    }
    public function scopeStart_date($query, $start_date)
    {
        return $query->whereDate('created_at', '>=', $start_date);
    }
    public function scopeEnd_date($query, $end_date)
    {
        return $query->whereDate('created_at', '<=', $end_date);
    }
    public function scopeDate_between($query, $start_date, $end_date)
    {
        return $query->whereBetween('created_at', [$start_date, $end_date]);
    }
    public function scopeOrder_type($query, $type)
    {
        return $query->where('type', $type);
    }
    // public function scopePayment_type($query, $payment_type)
    // {
    //     return $query->whereHas('payments', function ($query) use ($payment_type) {
    //         $query->where('payment_type', $payment_type);
    //     });
    // }
    public function scopeOrder_status($query, $order_status)
    {
        switch ($order_status) {
            case 'open':
                return $query->where('state', '!=', 'closed');
            case 'closed':
                return $query->where('state', 'closed');
        }
    }
    public function scopeCustomer_ids($query, $customer_ids)
    {
        return $query->where('customer_id', $customer_ids);
    }
    public function scopeCustomer_name($query, $customer_name)
    {
        return $query->whereHas('customer', function ($query) use ($customer_name) {
            $query->where('name', 'LIKE', '%' . $customer_name . '%');
        });
    }
    public function scopeOrder_takers($query, $user_ids)
    {
        return $query->whereIn('user_id', $user_ids);
    }
    public function scopeShop_ids($query, $shop_ids)
    {
        return $query->whereIn('shop_id', $shop_ids);
    }
    public function scopeCashiers($query, $cashiers)
    {
        return $query->whereHas('payments', function ($query) use ($cashiers) {
            $query->whereIn('user_id', $cashiers);
        });
    }
    public function scopeItem_name($query, $item_name)
    {
        return $query->whereHas('items', function ($query) use ($item_name) {
            $query->where('product_name', 'LIKE', '%' . $item_name . '%');
        });
    }
    /**
     * Filter orders by item IDs.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array $item_ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeItem_ids($query, $item_ids)
    {
        return $query->whereHas('items', function ($query) use ($item_ids) {
            $query->where('product_id', $item_ids);
        });
    }
}
