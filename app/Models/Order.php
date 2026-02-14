<?php

namespace App\Models;

use App\Http\Controllers\OrderHistoryController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use HasFactory, LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];
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
        'notes',
        // Financial fields
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        // Offline sync fields
        'sync_status',
        'local_order_id',
        'device_id',
        'synced_at',
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

        static::creating(function ($order) {
            if (empty($order->POS_number)) {
                $order->POS_number = static::generatePOSNumberStatic();
            }
        });
    }

    public static function generatePOSNumberStatic()
    {
        $date = now()->format('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "POS-{$date}-{$random}";
    }
    public function assignPOS()
    {
        $posNumber = null;
        DB::transaction(function () {
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

    public function generatePOSNumber()
    {
        return static::generatePOSNumberStatic();
    }

    public function setStateAttribute($value)
    {
        $validStates = ['preparing', 'served', 'closed', 'wastage'];
        if (!in_array($value, $validStates)) {
            throw new \InvalidArgumentException("Invalid state: {$value}");
        }
        $this->attributes['state'] = $value;
    }

    public function setTypeAttribute($value)
    {
        if ($value === null) {
            $value = $this->attributes['type'] ?? 'dine-in';
        }
        
        $validTypes = ['dine-in', 'take-away', 'delivery'];
        if (!in_array($value, $validTypes)) {
            throw new \InvalidArgumentException("Invalid type: {$value}");
        }
        $this->attributes['type'] = $value;
    }

    public function canTransitionTo($newState)
    {
        $validTransitions = [
            'preparing' => ['served', 'closed', 'wastage'],
            'served' => ['closed'],
            'closed' => [],
            'wastage' => ['closed']
        ];

        return in_array($newState, $validTransitions[$this->state] ?? []);
    }

    public function transitionTo($newState)
    {
        if (!$this->canTransitionTo($newState)) {
            return false;
        }

        $this->state = $newState;
        $this->save();
        return true;
    }

    public function calculateTotal()
    {
        return $this->items->sum('total_price');
    }

    public function updateTotalAmount()
    {
        $this->total_amount = $this->calculateTotal();
        $this->save();
    }

    public function getDuration()
    {
        return $this->created_at->diffInMinutes($this->updated_at);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeByShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
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

        'pos_number' => 'pos_number',
        'table_number' => 'table_number',
        'waiter_name' => 'waiter_name',
        'type' => 'order_type',
        'order_status' => 'order_status',
            'customer_ids' => 'customer_ids',
        'customer_name' => 'customer_name',
        'order_taker' => 'order_taker',
            'order_takers' => 'order_takers',
        'shop_ids' => 'shop_ids',
            'cashiers' => 'cashiers',
        'item_name' => 'item_name',
    -----------------   SCOPES   -----------------
        - start_date
        - end_date
        - date_between
        - order_type >
    - payment_type
        - pos_number (like)
        - table_number
        - waiter_name
        - order_status [open|closed]
        - customer_ids >
        - customer_name
        - order_taker (search by name)
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
    public function scopeTable_number($query, $table_number)
    {
        return $query->where('table_number', 'LIKE', "%{$table_number}%");
    }
    public function scopeWaiter_name($query, $waiter_name)
    {
        return $query->where('waiter_name', 'LIKE', "%{$waiter_name}%");
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
    public function scopeOrder_taker($query, $order_taker_name)
    {
        return $query->whereHas('user', function ($query) use ($order_taker_name) {
            $query->where(function ($query) use ($order_taker_name) {
                $query->where('first_name', 'LIKE', '%' . $order_taker_name . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $order_taker_name . '%');
            });
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
    // public function scopeItem_ids($query, $item_ids)
    // {
    //     return $query->whereHas('items', function ($query) use ($item_ids) {
    //         $query->where('product_id', $item_ids);
    //     });
    // }
}
