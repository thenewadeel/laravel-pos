<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Attributes\On;
use Livewire\Component;

class ItemCard extends Component
{
    public $order;
    public $product;
    public $quantity;
    public $currentQuantity;
    public $message = 'X';
    public $processing = false;
    public $rules = [
        // 'item' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
    ];
    public function render()
    {
        return view('livewire.item-card');
    }
    public function mount($order, $product)
    {
        $this->order = $order;
        $this->product = $product;
        $this->setupQuantities();
    }
    public function setupQuantities()
    {
        if ($this->order->items->pluck('product.id')->contains($this->product->id)) {
            $this->currentQuantity = $this->order->items->where('product_id', $this->product->id)->first()->quantity;
        } else {
            $this->currentQuantity = 0;
        }
        // $this->quantity = $this->currentQuantity ? $this->currentQuantity : 1;
        $this->quantity = 1;
        $this->processing = false;
    }
    public function qtyUp()
    {
        $this->quantity++;
    }
    public function qtyDown()
    {
        if ($this->quantity > 1)
            $this->quantity--;
    }
    public function addProductToOrder()
    {
        $this->processing = true;

        $this->message =  'Product adding. . .';
        // dd($this->validate());
        // $product = Product::find($request->item);
        if ($this->validate()) {
            $this->message =  'Product adding. . .step 1';

            $product = $this->product;
            $order = $this->order;
            $quantity = $this->quantity;

            if ($existingItem = $order->items()->where('product_id', $product->id)->first()) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $quantity,
                    'price' => $product->price * ($existingItem->quantity + $quantity),
                ]);
            } else {
                $validatedData['product_id'] = $product->id;
                $validatedData['quantity'] = $quantity;
                $validatedData['price'] = $product->price * $quantity;
                $order->items()->create($validatedData);
            }
            // $order->items()->create($validatedData);
            $this->message = 'Product added to order successfully';
            // $this->reset();
            $this->setupQuantities();
            $this->dispatch('item-added-to-order', orderId: $order->id)->self();
            $this->dispatch('order-updated', orderId: $order->id);
        } else {
            $this->message =  'Product not added';
        }
        // return redirect()->route('orders.edit', $order)->with('success', 'Product added to order successfully');
    }

    #[On('item-added-to-order')]
    public function updateOrder($orderId)
    {
        if ($this->order->id == $orderId) {
            // $this->message = $orderId;
            $this->order = Order::find($this->order->id);
        }
        $this->setupQuantities();
    }
}
