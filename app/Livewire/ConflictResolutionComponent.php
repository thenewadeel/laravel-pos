<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderSyncQueue;
use App\Services\ConflictResolutionService;
use Livewire\Component;
use Livewire\WithPagination;

class ConflictResolutionComponent extends Component
{
    use WithPagination;

    public string $conflictTypeFilter = 'all';
    public string $searchQuery = '';
    public ?int $selectedConflictId = null;
    public array $conflictDetails = [];
    public bool $showResolutionModal = false;
    public string $resolutionStrategy = '';

    protected $listeners = ['conflict-resolved' => '$refresh', 'conflict-dismissed' => '$refresh'];

    protected ConflictResolutionService $conflictService;

    public function boot(ConflictResolutionService $conflictService)
    {
        $this->conflictService = $conflictService;
    }

    public function getConflictsProperty()
    {
        $query = OrderSyncQueue::with(['order', 'order.customer', 'order.items'])
            ->where('status', 'conflict')
            ->orderBy('updated_at', 'desc');

        if ($this->conflictTypeFilter !== 'all') {
            $query->whereJsonContains('conflict_data->type', $this->conflictTypeFilter);
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('local_order_id', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('device_id', 'like', '%' . $this->searchQuery . '%')
                  ->orWhereHas('order', function ($oq) {
                      $oq->where('table_number', 'like', '%' . $this->searchQuery . '%');
                  });
            });
        }

        return $query->paginate(20);
    }

    public function getConflictStatsProperty()
    {
        $total = OrderSyncQueue::where('status', 'conflict')->count();
        
        $byType = OrderSyncQueue::where('status', 'conflict')
            ->get()
            ->groupBy(function ($item) {
                return $item->conflict_data['type'] ?? 'unknown';
            })
            ->map->count();

        return [
            'total' => $total,
            'by_type' => $byType,
        ];
    }

    public function viewConflictDetails(int $syncQueueId)
    {
        $syncQueue = OrderSyncQueue::with(['order', 'order.items'])->find($syncQueueId);
        
        if ($syncQueue) {
            $this->selectedConflictId = $syncQueueId;
            $this->conflictDetails = [
                'sync_queue' => $syncQueue->toArray(),
                'order' => $syncQueue->order?->toArray(),
                'conflict_data' => $syncQueue->conflict_data,
            ];
            $this->showResolutionModal = true;
        }
    }

    public function resolveConflict(int $orderId, string $strategy)
    {
        $order = Order::with('items')->find($orderId);
        
        if (!$order) {
            session()->flash('error', 'Order not found');
            return;
        }

        $syncQueue = OrderSyncQueue::where('order_id', $orderId)
            ->where('status', 'conflict')
            ->first();

        if (!$syncQueue) {
            session()->flash('error', 'Conflict not found');
            return;
        }

        try {
            // Build order data from the order
            $orderData = [
                'shop_id' => $order->shop_id,
                'user_id' => $order->user_id,
                'customer_id' => $order->customer_id,
                'table_number' => $order->table_number,
                'waiter_name' => $order->waiter_name,
                'type' => $order->type,
                'items' => $order->items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ])->toArray(),
                'subtotal' => $order->subtotal,
                'total_amount' => $order->total_amount,
                'device_id' => $order->device_id,
                'local_order_id' => $order->local_order_id,
            ];

            // Check if there's an existing order to resolve against
            $conflictData = $syncQueue->conflict_data;
            $existingOrder = null;
            
            if (isset($conflictData['existing_order_id'])) {
                $existingOrder = Order::find($conflictData['existing_order_id']);
            }

            if ($existingOrder && in_array($strategy, ['use_server', 'update_server', 'merge'])) {
                $result = $this->conflictService->resolveConflict($orderData, $existingOrder, $strategy);
            } elseif ($strategy === 'merge' && $existingOrder) {
                $result = $this->conflictService->mergeOrders($existingOrder, $orderData);
            } else {
                // For strategies that don't need existing order
                $order->update(['sync_status' => 'synced', 'synced_at' => now()]);
                $syncQueue->markAsCompleted();
                $result = ['success' => true, 'resolution' => $strategy];
            }

            if ($result['success'] ?? false) {
                session()->flash('message', 'Conflict resolved successfully');
                $this->dispatch('conflict-resolved', ['order_id' => $orderId]);
            } else {
                session()->flash('error', 'Failed to resolve conflict');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error resolving conflict: ' . $e->getMessage());
        }

        $this->showResolutionModal = false;
        $this->selectedConflictId = null;
        $this->conflictDetails = [];
    }

    public function resolveInventoryConflict(int $orderId, string $strategy)
    {
        $order = Order::with('items')->find($orderId);
        
        if (!$order) {
            session()->flash('error', 'Order not found');
            return;
        }

        $syncQueue = OrderSyncQueue::where('order_id', $orderId)
            ->where('status', 'conflict')
            ->first();

        if (!$syncQueue) {
            session()->flash('error', 'Conflict not found');
            return;
        }

        try {
            $items = $order->items->map(fn($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ])->toArray();

            $result = $this->conflictService->resolveInventoryConflict($order, $items, $strategy);

            if ($result['success']) {
                $syncQueue->markAsCompleted();
                session()->flash('message', 'Inventory conflict resolved');
                $this->dispatch('conflict-resolved', ['order_id' => $orderId]);
            } else {
                session()->flash('error', 'Failed to resolve inventory conflict');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function dismissConflict(int $orderId)
    {
        $syncQueue = OrderSyncQueue::where('order_id', $orderId)
            ->where('status', 'conflict')
            ->first();

        if ($syncQueue) {
            // Mark as failed/dismissed
            $syncQueue->update([
                'status' => 'failed',
                'error_message' => 'Manually dismissed by user',
            ]);

            session()->flash('message', 'Conflict dismissed');
            $this->dispatch('conflict-dismissed', ['order_id' => $orderId]);
        }

        $this->showResolutionModal = false;
    }

    public function closeModal()
    {
        $this->showResolutionModal = false;
        $this->selectedConflictId = null;
        $this->conflictDetails = [];
    }

    public function clearFilters()
    {
        $this->conflictTypeFilter = 'all';
        $this->searchQuery = '';
    }

    public function render()
    {
        return view('livewire.conflict-resolution-component', [
            'conflicts' => $this->conflicts,
            'conflictStats' => $this->conflictStats,
        ]);
    }
}
