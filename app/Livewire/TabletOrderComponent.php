<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Product;
use App\Services\OfflineOrderService;
use Illuminate\Support\Str;
use Livewire\Component;

class TabletOrderComponent extends Component
{
    // Device and shop identification
    public string $deviceId = '';
    public int $shopId = 0;
    
    // Order details
    public string $tableNumber = '';
    public string $waiterName = '';
    public string $orderType = 'dine-in';
    public ?int $customerId = null;
    public string $localOrderId = '';
    
    // Order items
    public array $orderItems = [];
    public float $totalAmount = 0.00;
    
    // UI state
    public bool $isOnline = true;
    public bool $orderCreated = false;
    public string $productSearch = '';
    public string $errorMessage = '';
    
    // Services
    protected OfflineOrderService $orderService;
    
    public function boot(OfflineOrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    
    public function mount()
    {
        $this->generateLocalOrderId();
        $this->waiterName = auth()->user()?->first_name ?? '';
    }
    
    public function generateLocalOrderId()
    {
        $timestamp = now()->format('YmdHis');
        $random = Str::random(6);
        $this->localOrderId = "{$this->deviceId}-{$timestamp}-{$random}";
    }
    
    public function addItem(int $productId, int $quantity = 1)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            $this->errorMessage = 'Product not found';
            return;
        }
        
        if ($product->quantity < $quantity) {
            $this->errorMessage = "Insufficient stock for {$product->name}. Available: {$product->quantity}";
            $this->addError('orderItems', $this->errorMessage);
            return;
        }
        
        // Check if item already exists
        $existingIndex = null;
        foreach ($this->orderItems as $index => $item) {
            if ($item['product_id'] === $productId) {
                $existingIndex = $index;
                break;
            }
        }
        
        if ($existingIndex !== null) {
            // Update existing item
            $newQuantity = $this->orderItems[$existingIndex]['quantity'] + $quantity;
            if ($product->quantity < $newQuantity) {
                $this->errorMessage = "Insufficient stock for {$product->name}. Available: {$product->quantity}";
                $this->addError('orderItems', $this->errorMessage);
                return;
            }
            $this->orderItems[$existingIndex]['quantity'] = $newQuantity;
            $this->orderItems[$existingIndex]['total_price'] = $newQuantity * $product->price;
        } else {
            // Add new item
            $this->orderItems[] = [
                'product_id' => $productId,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total_price' => $quantity * $product->price,
            ];
        }
        
        $this->calculateTotal();
        $this->errorMessage = '';
    }
    
    public function removeItem(int $index)
    {
        if (isset($this->orderItems[$index])) {
            unset($this->orderItems[$index]);
            $this->orderItems = array_values($this->orderItems); // Re-index array
        }
        $this->calculateTotal();
    }
    
    public function updateQuantity(int $index, int $quantity)
    {
        if (!isset($this->orderItems[$index])) {
            return;
        }
        
        if ($quantity <= 0) {
            $this->removeItem($index);
            return;
        }
        
        $productId = $this->orderItems[$index]['product_id'];
        $product = Product::find($productId);
        
        if ($product && $product->quantity < $quantity) {
            $this->errorMessage = "Insufficient stock for {$product->name}. Available: {$product->quantity}";
            return;
        }
        
        $this->orderItems[$index]['quantity'] = $quantity;
        $this->orderItems[$index]['total_price'] = $quantity * $this->orderItems[$index]['unit_price'];
        $this->calculateTotal();
    }
    
    public function calculateTotal()
    {
        $this->totalAmount = collect($this->orderItems)->sum('total_price');
    }
    
    public function createOrder()
    {
        $this->validate([
            'tableNumber' => 'required|string',
            'orderItems' => 'required|array|min:1',
            'shopId' => 'required|integer|exists:shops,id',
            'deviceId' => 'required|string',
        ], [
            'tableNumber.required' => 'Table number is required',
            'orderItems.required' => 'Please add at least one item',
            'orderItems.min' => 'Please add at least one item',
        ]);
        
        $orderData = [
            'shop_id' => $this->shopId,
            'user_id' => auth()->id(),
            'customer_id' => $this->customerId,
            'table_number' => $this->tableNumber,
            'waiter_name' => $this->waiterName,
            'type' => $this->orderType,
            'items' => $this->orderItems,
            'subtotal' => $this->totalAmount,
            'total_amount' => $this->totalAmount,
            'device_id' => $this->deviceId,
            'local_order_id' => $this->localOrderId,
        ];
        
        try {
            $order = $this->orderService->createOfflineOrder($orderData);
            
            if ($order) {
                $this->orderCreated = true;
                $this->clearOrderData();
                $this->dispatch('order-created', ['local_order_id' => $this->localOrderId]);
            } else {
                $this->errorMessage = 'Order already exists or could not be created';
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }
    
    public function clearOrder()
    {
        $this->clearOrderData();
        $this->orderCreated = false;
    }
    
    private function clearOrderData()
    {
        $this->orderItems = [];
        $this->totalAmount = 0.00;
        $this->tableNumber = '';
        $this->customerId = null;
        $this->errorMessage = '';
        $this->generateLocalOrderId();
    }
    
    public function dismissSuccessMessage()
    {
        $this->orderCreated = false;
    }
    
    public function getProductsProperty()
    {
        $query = Product::query()->where('aval_status', true);
        
        if (!empty($this->productSearch)) {
            $query->where('name', 'like', '%' . $this->productSearch . '%');
        }
        
        return $query->limit(50)->get();
    }
    
    public function getCustomersProperty()
    {
        return Customer::limit(20)->get();
    }
    
    public function render()
    {
        return view('livewire.tablet-order-component', [
            'products' => $this->products,
            'customers' => $this->customers,
        ]);
    }
}
