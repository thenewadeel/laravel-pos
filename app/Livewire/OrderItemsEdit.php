<?php

namespace App\Livewire;

use App\Http\Controllers\OrderHistoryController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\On;

class OrderItemsEdit extends Component
{
    public $order;
    public $message = 'asd';
    public $miscProductName, $miscProductPrice;
    protected $rules = [
        "miscProductName" => "required|string",
        "miscProductPrice" => "required|decimal:0,2",
    ];
    // public $products;
    public function render()
    {
        return view('livewire.order-items-edit');
    }
    public function addMiscProduct()
    {
        $this->validate();
        $product = Product::firstOrCreate([
            'name' => $this->miscProductName,
            'price' => $this->miscProductPrice
        ]);

        $this->order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
            'product_rate' => $product->price,
            'product_name' => $product->name,
        ]);

        // Create order history
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request = null, $this->order->id, 'item-added', $itemName = $product->name, $itemQty = 1);


        $this->dispatch('order-updated', orderId: $this->order->id);
    }
    public function deleteItem($id)
    {
        $item = OrderItem::find($id);
        // dd($itemname);
        // Create order history
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request = null, orderId: $this->order->id, actionType: 'item-removed', itemName: $item->product->name, itemQty: 0);
        if ($item->order->id == $this->order->id) {
            $item->delete();
        }
        $this->dispatch('order-updated', orderId: $this->order->id);
    }
    public function increaseQty($itemId)
    {
        $item = OrderItem::find($itemId);
        $newQty = $item->quantity + 1;
        $item->update([
            'quantity' => $newQty,
            'price' => $item->product->price * ($newQty)
        ]);

        // Create order history
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request = null, $this->order->id, 'item-added', $itemName = $item->product->name, $itemQty = $newQty);

        $this->dispatch('order-updated', orderId: $this->order->id);
    }
    public function decreaseQty($itemId)
    {
        $item = OrderItem::find($itemId);
        if ($item->quantity > 1) {
            $newQty = $item->quantity - 1;
            $item->update([
                'quantity' => $newQty,
                'price' => $item->product->price * ($newQty)
            ]);

            // Create order history
            $orderHistoryController = new OrderHistoryController();
            $orderHistoryController->store($request = null, $this->order->id, 'item-removed', $itemName = $item->product->name, $itemQty = $newQty);

            $this->dispatch('order-updated', orderId: $this->order->id);
        }
    }
    // #[On('item-added-to-order')]
    #[On('order-updated')]
    public function updatePostList($orderId)
    {
        if ($this->order->id == $orderId) {
            $this->message = $orderId;
            $this->order = Order::find($this->order->id);
        }
    }

    public function toggleDiscount($discountId)
    {
        $prevDiscounts = $this->order->discounts()->get();

        if ($this->order->discounts->contains($discountId)) {
            $this->order->discounts()->detach($discountId);
        } else {
            $this->order->discounts()->attach($discountId);
        }


        // Create order history
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request = null, orderId: $this->order->id, actionType: 'discount-changed');


        activity('order-discount')
            ->causedBy(auth()->user())
            ->performedOn($this->order)
            ->withProperties(['old' => $prevDiscounts, 'attributes' => $this->order->discounts()->get()])
            ->log('edited');

        $this->dispatch('order-updated', orderId: $this->order->id);

        $this->order = Order::find($this->order->id);
    }
}
