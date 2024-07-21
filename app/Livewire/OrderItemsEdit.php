<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Component;
use Livewire\Attributes\On;

class OrderItemsEdit extends Component
{
    public $order;
    public $message = 'asd';
    // public $products;
    public function render()
    {
        return view('livewire.order-items-edit');
    }
    public function deleteItem($id)
    {
        $item = OrderItem::find($id);
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
            $this->dispatch('order-updated', orderId: $this->order->id);
        }
    }
    // #[On('order-updated')]
    #[On('item-added-to-order')]
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

        activity('order-discount')
            ->causedBy(auth()->user())
            ->performedOn($this->order)
            ->withProperties(['old' => $prevDiscounts, 'attributes' => $this->order->discounts()->get()])
            ->log('edited');

        $this->dispatch('order-updated', orderId: $this->order->id);

        $this->order = Order::find($this->order->id);
    }
}
