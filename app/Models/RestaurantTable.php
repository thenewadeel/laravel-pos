<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'restaurant_tables';

    protected $fillable = [
        'floor_id',
        'table_number',
        'name',
        'capacity',
        'status',
        'position_x',
        'position_y',
        'width',
        'height',
        'shape',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'position_x' => 'decimal:2',
        'position_y' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'table_orders', 'table_id', 'order_id')
            ->withPivot('started_at', 'ended_at', 'is_active')
            ->withTimestamps();
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeForFloor($query, $floorId)
    {
        return $query->where('floor_id', $floorId);
    }

    public function getActiveOrder()
    {
        return $this->orders()
            ->wherePivot('is_active', true)
            ->first();
    }

    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
    }

    public function assignOrder($orderId)
    {
        $this->orders()->attach($orderId, [
            'started_at' => now(),
            'is_active' => true,
        ]);
        
        $this->updateStatus('occupied');
    }

    public function release()
    {
        $activeOrder = $this->getActiveOrder();
        
        if ($activeOrder) {
            $this->orders()->updateExistingPivot($activeOrder->id, [
                'is_active' => false,
                'ended_at' => now(),
            ]);
        }
        
        $this->updateStatus('available');
    }

    public function isOccupied()
    {
        return $this->status === 'occupied';
    }

    public function getOccupationDuration()
    {
        $activeOrder = $this->getActiveOrder();
        
        if (!$activeOrder) {
            return 0;
        }
        
        $startedAt = $this->orders()
            ->where('order_id', $activeOrder->id)
            ->first()
            ->pivot
            ->started_at;
        
        return now()->diffInMinutes($startedAt);
    }
}
