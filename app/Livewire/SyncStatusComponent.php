<?php

namespace App\Livewire;

use App\Jobs\ProcessOfflineSyncQueue;
use App\Models\DeviceSyncLog;
use App\Models\Order;
use App\Models\OrderSyncQueue;
use App\Services\OfflineOrderService;
use Livewire\Component;
use Livewire\WithPagination;

class SyncStatusComponent extends Component
{
    use WithPagination;
    
    public string $selectedDevice = '';
    public string $searchQuery = '';
    public string $statusFilter = 'all';
    public bool $autoRefresh = false;
    public int $refreshInterval = 30; // seconds
    
    protected $listeners = ['status-refreshed' => '$refresh'];
    
    protected OfflineOrderService $orderService;
    
    public function boot(OfflineOrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    
    public function mount()
    {
        // Default to showing all devices
    }
    
    public function getDevicesProperty()
    {
        return Order::select('device_id')
            ->distinct()
            ->whereNotNull('device_id')
            ->pluck('device_id')
            ->toArray();
    }
    
    public function getDeviceStatsProperty()
    {
        $query = Order::query();
        
        if ($this->selectedDevice) {
            $query->where('device_id', $this->selectedDevice);
        }
        
        $total = $query->count();
        $pending = (clone $query)->where('sync_status', 'pending_sync')->count();
        $synced = (clone $query)->where('sync_status', 'synced')->count();
        
        $failed = OrderSyncQueue::when($this->selectedDevice, function ($q) {
            $q->where('device_id', $this->selectedDevice);
        })->where('status', 'failed')->count();
        
        $conflicts = OrderSyncQueue::when($this->selectedDevice, function ($q) {
            $q->where('device_id', $this->selectedDevice);
        })->where('status', 'conflict')->count();
        
        return [
            'total' => $total,
            'pending' => $pending,
            'synced' => $synced,
            'failed' => $failed,
            'conflicts' => $conflicts,
        ];
    }
    
    public function getOrdersProperty()
    {
        $query = Order::with(['items', 'customer'])
            ->whereNotNull('device_id')
            ->orderBy('created_at', 'desc');
        
        if ($this->selectedDevice) {
            $query->where('device_id', $this->selectedDevice);
        }
        
        if ($this->statusFilter !== 'all') {
            $query->where('sync_status', $this->statusFilter);
        }
        
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('table_number', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('local_order_id', 'like', '%' . $this->searchQuery . '%')
                  ->orWhereHas('customer', function ($cq) {
                      $cq->where('name', 'like', '%' . $this->searchQuery . '%');
                  });
            });
        }
        
        return $query->paginate(20);
    }
    
    public function getFailedSyncsProperty()
    {
        return OrderSyncQueue::with('order')
            ->when($this->selectedDevice, function ($q) {
                $q->where('device_id', $this->selectedDevice);
            })
            ->where('status', 'failed')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    public function getLastSyncTimeProperty()
    {
        $lastSync = DeviceSyncLog::when($this->selectedDevice, function ($q) {
            $q->where('device_id', $this->selectedDevice);
        })
        ->where('action', 'order_synced')
        ->latest()
        ->first();
        
        return $lastSync?->created_at?->diffForHumans() ?? 'Never';
    }
    
    public function syncDevice(string $deviceId)
    {
        // Dispatch job to sync all pending orders for this device
        ProcessOfflineSyncQueue::dispatch(null, $deviceId);
        
        $this->dispatch('sync-started', ['device_id' => $deviceId]);
        
        session()->flash('message', "Sync initiated for device: {$deviceId}");
    }
    
    public function syncAll()
    {
        ProcessOfflineSyncQueue::dispatch(null, null, true);
        
        $this->dispatch('sync-all-started');
        
        session()->flash('message', 'Sync initiated for all pending orders');
    }
    
    public function retryFailed(int $syncQueueId)
    {
        $syncQueue = OrderSyncQueue::find($syncQueueId);
        
        if ($syncQueue) {
            $syncQueue->update([
                'status' => 'pending',
                'retry_count' => 0,
                'error_message' => null,
            ]);
            
            // Dispatch job for this specific order
            ProcessOfflineSyncQueue::dispatch($syncQueue->order_id);
            
            session()->flash('message', 'Retry initiated for order');
        }
    }
    
    public function refreshStatus()
    {
        $this->dispatch('status-refreshed');
    }
    
    public function clearFilters()
    {
        $this->selectedDevice = '';
        $this->searchQuery = '';
        $this->statusFilter = 'all';
    }
    
    public function render()
    {
        return view('livewire.sync-status-component', [
            'devices' => $this->devices,
            'deviceStats' => $this->deviceStats,
            'orders' => $this->orders,
            'failedSyncs' => $this->failedSyncs,
            'lastSyncTime' => $this->lastSyncTime,
        ]);
    }
}
