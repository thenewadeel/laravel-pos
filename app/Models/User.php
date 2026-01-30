<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, LogsActivity;
    
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'type',
        'current_shop_id'
    ];

    public function getFavoritePrinterIpAttribute()
    {
        return $this->fav_printer_ip;
    }

    public function setFavoritePrinterIpAttribute($value)
    {
        $this->fav_printer_ip = $value;
    }

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('users')
            ->logFillable()
            ->logOnlyDirty();
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            $user->validateUniqueEmail();
        });
    }

    public function validateUniqueEmail()
    {
        if ($this->isDirty('email') && $this->email) {
            if (static::where('email', $this->email)->where('id', '!=', $this->id)->exists()) {
                throw new \Exception('Email already exists');
            }
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email format');
            }
        }
    }

    public function getFullName()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function name()
    {
        return $this->getFullName();
    }

    public function getPhoto()
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email)));
    }

    public function cart()
    {
        return $this->belongsToMany(Product::class, 'user_cart')->withPivot('quantity');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'user_shop')->withTimestamps();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function setCurrentShop($shopId)
    {
        $this->current_shop_id = $shopId;
        $this->save();
    }

    public function getActiveShop()
    {
        if ($this->current_shop_id) {
            return $this->shops()->where('shop_id', $this->current_shop_id)->first();
        }
        return $this->shops()->first();
    }

    public function hasShopAccess($shopId)
    {
        return $this->shops()->where('shop_id', $shopId)->exists();
    }

    public function getOrdersByShop($shopId)
    {
        return $this->orders()->where('shop_id', $shopId)->get();
    }

    public function getOrderStatistics()
    {
        $totalOrders = $this->orders()->count();
        $completedOrders = $this->orders()->where('state', 'closed')->count();
        $pendingOrders = $this->orders()->whereIn('state', ['preparing', 'served'])->count();
        $totalRevenue = $this->orders()->sum('total_amount') ?: 0;

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'total_revenue' => $totalRevenue,
        ];
    }

    public function getAverageOrderValue()
    {
        $total = $this->orders()->sum('total_amount') ?: 0;
        $count = $this->orders()->count();
        return $count > 0 ? $total / $count : 0;
    }

    public function getMostRecentOrder()
    {
        return $this->orders()->latest()->first();
    }

    public function isWaiter()
    {
        return $this->type === 'cashier' || $this->type === 'waiter';
    }

    public function isManager()
    {
        return $this->type === 'admin' || $this->type === 'manager';
    }

    public function getWorkingHours($startDate, $endDate)
    {
        $orders = $this->orders()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalHours = 0;
        foreach ($orders as $order) {
            if ($order->created_at && $order->updated_at) {
                $hours = $order->created_at->diffInHours($order->updated_at);
                $totalHours += $hours;
            }
        }

        return $totalHours;
    }

    public function getShiftSummary($shopId, $date)
    {
        $orders = $this->orders()
            ->where('shop_id', $shopId)
            ->whereDate('created_at', $date)
            ->get();

        $totalRevenue = $orders->sum('total_amount');
        $orderCount = $orders->count();
        $averageOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        return [
            'orders_count' => $orderCount,
            'total_revenue' => $totalRevenue,
            'average_order_value' => round($averageOrderValue, 2),
        ];
    }

    public function scopeByShop($query, $shopId)
    {
        return $query->whereHas('shops', function ($q) use ($shopId) {
            $q->where('shop_id', $shopId);
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%");
        });
    }

    public function getPerformanceMetrics($startDate, $endDate)
    {
        $orders = $this->orders()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $days = $startDate->diffInDays($endDate) ?: 1;
        $ordersPerDay = $totalOrders / $days;

        return [
            'orders_processed' => $totalOrders,
            'revenue_generated' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'orders_per_day' => round($ordersPerDay, 2),
        ];
    }

    public static function isPasswordStrong($password)
    {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password) &&
               preg_match('/[^A-Za-z0-9]/', $password);
    }

    public function assignRole($role)
    {
        // Map test roles to actual database enum values
        $roleMap = [
            'waiter' => 'cashier',
            'manager' => 'admin',
        ];
        
        $this->type = $roleMap[$role] ?? $role;
        $this->save();
    }

    public function getPermissions()
    {
        $permissions = [];
        
        switch ($this->type) {
            case 'admin':
            case 'manager':
                $permissions = ['create_orders', 'view_orders', 'manage_users', 'delete_shop', 'view_reports'];
                break;
            case 'cashier':
            case 'waiter':
                $permissions = ['create_orders', 'view_orders'];
                break;
            case 'chef':
                $permissions = ['view_orders'];
                break;
            case 'stockBoy':
                $permissions = ['view_inventory'];
                break;
        }

        return $permissions;
    }
}
