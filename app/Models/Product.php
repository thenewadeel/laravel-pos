<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use AliBayat\LaravelCategorizable\Categorizable;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Categorizable;

    protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'quantity',
        'aval_status', // maps to status in tests
        'kitchen_printer_ip',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'aval_status' => 'boolean',
    ];

    protected $attributes = [
        'quantity' => 1000,
        'aval_status' => true,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('products')
            ->logFillable()
            ->logOnlyDirty();
    }

    // Relationships
    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'product_shop')->withTimestamps();
    }

    // Categories relationship is handled by Categorizable trait

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_products')
            ->withTimestamps()
            ->withPivot('name');
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, OrderItem::class, 'product_id', 'id', 'id', 'order_id');
    }

    // Accessors & Mutators
    public function getStatusAttribute()
    {
        return $this->aval_status;
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['aval_status'] = $value;
    }

    // Helper Methods
    public function isAvailable(): bool
    {
        return $this->quantity > 0 && $this->aval_status;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= 10; // Low stock threshold is 10
    }

    public function updateQuantity(int $newQuantity): void
    {
        if ($newQuantity < 0) {
            throw new \Exception('Insufficient quantity available');
        }

        $oldQuantity = $this->quantity;
        $this->quantity = $newQuantity;
        $this->save();

        // Log the quantity change
        activity()
            ->causedBy(auth()->user())
            ->performedOn($this)
            ->withProperties([
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
            ])
            ->log('quantity_updated');
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getPriceWithoutSymbol(): string
    {
        return number_format($this->price, 2);
    }

    public function getTotalValue(): float
    {
        return $this->price * $this->quantity;
    }

    // Query Scopes
    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0)->where('aval_status', true);
    }

    public function scopeUnavailable($query)
    {
        return $query->where(function ($q) {
            $q->where('quantity', '<=', 0)->orWhere('aval_status', false);
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%");
    }

    public function scopePopular($query)
    {
        // For now, return products ordered by quantity sold (placeholder logic)
        // This would need to be implemented based on order items
        return $query->orderBy('quantity', 'desc');
    }

    // Validation rules
    public static $rules = [
        'name' => 'required|string|max:191',
        'description' => 'nullable|string',
        'image' => 'nullable|string|max:191',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
        'aval_status' => 'boolean',
        'kitchen_printer_ip' => 'nullable|ip',
    ];

    // Model Events for activity logging and validation
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($product) {
            // Validate price is positive
            if ($product->price < 0) {
                throw new \Exception('Price must be a positive number');
            }
            
            // Validate quantity is non-negative
            if ($product->quantity < 0) {
                throw new \Exception('Quantity cannot be negative');
            }
            
            // Validate required fields
            if (empty($product->name) || empty($product->price) || !isset($product->quantity)) {
                throw new \Exception('Required fields are missing');
            }
        });

        static::created(function ($product) {
            activity()
                ->causedBy(auth()->user() ?? null)
                ->performedOn($product)
                ->log('created');
        });

        static::updated(function ($product) {
            if ($product->wasChanged()) {
                activity()
                    ->causedBy(auth()->user() ?? null)
                    ->performedOn($product)
                    ->log('updated');
            }
        });

        static::deleted(function ($product) {
            activity()
                ->causedBy(auth()->user() ?? null)
                ->performedOn($product)
                ->log('deleted');
        });
    }
}
