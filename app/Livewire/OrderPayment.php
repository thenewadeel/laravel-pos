<?php

namespace App\Livewire;

use App\Http\Controllers\OrderHistoryController;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\On;

class OrderPayment extends Component
{
    public $order;
    public $orderBalance, $modal_title, $modal_message, $change;
    public $showModal;
    public function mount(Order $order)
    {
        // Log::info('OrderPayment mounted', ['order' => $order]);
        $this->order = $order;
        $this->showModal = false;
        $this->modal_title = __('order.Payment_Title');
        $this->initializeOrderBalance();
        // __('order.Payment_Text')
        // __('order.Part_Chit_Title')
        // __('order.Part_Chit_Text')
        // __('order.Part_Chit_Warning_Title')
        // __('order.Part_Chit_Warning_Text')
        // __('order.Change_Title')
        // __('order.Change_Text')
    }
    public function initializeOrderBalance()
    {
        $this->orderBalance = $this->order->balance();
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
            $this->order = Order::find($this->order->id);
        }
        $this->initializeOrderBalance();
    }

    public function checkPayment()
    {
        if ($this->orderBalance == $this->order->balance()) {
            $this->modal_title = __('order.Payment_Title');
            $this->modal_message = __('order.Payment_Text');
        } elseif ($this->orderBalance < $this->order->balance()) {
            $this->modal_title = __('order.Part_Chit_Title');
            $this->modal_message = __('order.Part_Chit_Text');
            $this->change = $this->orderBalance - $this->order->balance();
        } else {
            $this->modal_title = __('order.Change_Title');
            $this->modal_message = __('order.Change_Text');
            $this->change = $this->orderBalance - $this->order->balance();
        }
        $this->showModal = true;
    }

    public function payAndClose()
    {
        if ($this->order->POS_number == null) $this->order->assignPOS();
        if ($this->order->POS_number != null) {
            $amt = ($this->orderBalance >= $this->order->balance()) ? $this->order->balance() : $this->orderBalance;
            $this->order->payments()->create([
                'order_id' => $this->order->id,
                'user_id' => auth()->user()->id,
                'amount' => $amt,
            ]);

            // Create order history
            $orderHistoryController = new OrderHistoryController();
            $orderHistoryController->store($request = null, orderId: $this->order->id, actionType: 'payment-added', paymentAmount: $amt);

            $this->order->state = 'closed';
            $this->order->bakeOrder();
            $this->order->save();

            // Create order history
            $orderHistoryController = new OrderHistoryController();
            $orderHistoryController->store($request = null, orderId: $this->order->id, actionType: 'closed');

            return $this->redirect('/orders/' . $this->order->id);
        }
    }
}
