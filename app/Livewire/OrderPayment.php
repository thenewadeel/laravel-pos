<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\On;

class OrderPayment extends Component
{
    public $order;
    public function mount(Order $order)
    {
        $this->order = $order;
    }
    public function render()
    {
        return view('livewire.order-payment');
    }

    // #[On('item-added-to-order')]
    #[On('order-updated')]
    public function updateOrder($orderId)
    {
        if ($this->order->id == $orderId) {
            // $this->message = $orderId;
            $this->order = Order::find($this->order->id);
        }
    }
}
