<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use AliBayat\LaravelCategorizable\Categorizable;

class Customer extends Model
{
    use HasFactory, Categorizable, SoftDeletes, LogsActivity;
    
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'membership_number',
        'email',
        'phone',
        'address',
        'photo',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('customers')
            ->logFillable()
            ->logOnlyDirty();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->membership_number)) {
                $customer->membership_number = static::generateMembershipNumber();
            }
        });

        static::saving(function ($customer) {
            $customer->validateUniqueFields();
        });
    }

    public static function generateMembershipNumber()
    {
        do {
            $number = 'MEM-' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (static::where('membership_number', $number)->exists());
        
        return $number;
    }

    public function validateUniqueFields()
    {
        if ($this->isDirty('email') && $this->email) {
            if (static::where('email', $this->email)->where('id', '!=', $this->id)->exists()) {
                throw new \Exception('Email already exists');
            }
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email format');
            }
        }

        if ($this->isDirty('phone') && $this->phone) {
            $cleanedPhone = preg_replace('/[^0-9+]/', '', $this->phone);
            if (!preg_match('/^[\+]?[1-9][\d]{6,14}$/', $cleanedPhone)) {
                throw new \Exception('Invalid phone format');
            }
        }

        if ($this->isDirty('membership_number')) {
            if (static::where('membership_number', $this->membership_number)->where('id', '!=', $this->id)->exists()) {
                throw new \Exception('Membership number already exists');
            }
        }
    }

    public function getFullName()
    {
        if ($this->first_name && $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        return $this->name;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalOrdersAmount()
    {
        return $this->orders()->sum('total_amount') ?: 0;
    }

    public function getOrdersByStatus()
    {
        return $this->orders()
            ->selectRaw('state, COUNT(*) as count')
            ->groupBy('state')
            ->pluck('count', 'state')
            ->toArray();
    }

    public function getAverageOrderValue()
    {
        $total = $this->getTotalOrdersAmount();
        $count = $this->orders()->count();
        return $count > 0 ? $total / $count : 0;
    }

    public function isVIP()
    {
        return $this->getTotalOrdersAmount() > 1000 || $this->orders()->count() > 15;
    }

    public function scopeByMembershipNumber($query, $membershipNumber)
    {
        return $query->where('membership_number', $membershipNumber);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%");
        });
    }

    public function scopeSearchByPhone($query, $phone)
    {
        return $query->where('phone', 'like', "%{$phone}%");
    }

    public function getMostRecentOrder()
    {
        return $this->orders()->latest()->first();
    }

    public function getOrderHistorySummary()
    {
        $orders = $this->orders()->with('items')->get();
        
        return [
            'orders' => $orders,
            'total_amount' => $orders->sum('total_amount'),
            'average_amount' => $orders->count() > 0 ? $orders->avg('total_amount') : 0,
            'dine_in_count' => $orders->where('type', 'dine-in')->count(),
            'take_away_count' => $orders->where('type', 'take-away')->count(),
            'delivery_count' => $orders->where('type', 'delivery')->count(),
        ];
    }

    public function getStatistics()
    {
        $totalOrders = $this->orders()->count();
        $completedOrders = $this->orders()->where('state', 'closed')->count();
        $pendingOrders = $this->orders()->whereIn('state', ['preparing', 'served'])->count();
        $totalSpent = $this->getTotalOrdersAmount();
        $averageOrderValue = $this->getAverageOrderValue();

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'total_spent' => $totalSpent,
            'average_order_value' => round($averageOrderValue, 2),
        ];
    }

    public function scopeVip($query)
    {
        return $query->whereHas('orders', function ($q) {
            $q->selectRaw('customer_id, SUM(total_amount) as total_spent, COUNT(*) as order_count')
              ->groupBy('customer_id')
              ->havingRaw('SUM(total_amount) > 1000 OR COUNT(*) > 15');
        });
    }

    public function scopeRegular($query)
    {
        return $query->whereDoesntHave('orders', function ($q) {
            $q->selectRaw('customer_id, SUM(total_amount) as total_spent, COUNT(*) as order_count')
              ->groupBy('customer_id')
              ->havingRaw('total_spent > 1000 OR order_count > 15');
        })->orWhereDoesntHave('orders');
    }

    public function getLoyaltyTier()
    {
        $totalOrders = $this->orders()->where('state', 'closed')->count();
        $averageValue = $this->getAverageOrderValue();

        if ($totalOrders >= 25 && $averageValue >= 75) {
            return 'gold';
        } elseif ($totalOrders >= 15 && $averageValue >= 40) {
            return 'silver';
        } elseif ($totalOrders >= 5) {
            return 'bronze';
        }

        return 'bronze';
    }

    public function getPhotoUrl()
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }
}