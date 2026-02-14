<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSyncQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'device_id',
        'local_order_id',
        'sync_type',
        'status',
        'retry_count',
        'last_attempt_at',
        'completed_at',
        'error_message',
        'conflict_data',
    ];

    protected $casts = [
        'retry_count' => 'integer',
        'last_attempt_at' => 'datetime',
        'completed_at' => 'datetime',
        'conflict_data' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForDevice($query, string $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'last_attempt_at' => now(),
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsConflict(array $conflictData): void
    {
        $this->update([
            'status' => 'conflict',
            'conflict_data' => $conflictData,
        ]);
    }
}
