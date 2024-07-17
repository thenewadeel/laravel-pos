<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class OrderPOSNo extends Component
{
    public $order;
    public function mount(Order $order)
    {
        $this->order = $order;
        // Log::info("Order received in mount :");
        // Log::info( $order);
    }
    public function render()
    {
        return view('livewire.order-p-o-s-no');
    }
    public function saveOrder()
    {
        $this->order->assignPOS();
    }
}
