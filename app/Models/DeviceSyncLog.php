<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'order_id',
        'action',
        'status',
        'details',
        'ip_address',
        'sync_timestamp',
    ];

    protected $casts = [
        'sync_timestamp' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeForDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}
