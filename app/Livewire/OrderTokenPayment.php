<?php

namespace App\Livewire;

use App\Models\Order;
use Exception;
use Livewire\Component;
use Livewire\Attributes\On;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class OrderTokenPayment extends Component
{
    public $order, $err;
    public $printer_ip = '192.168.0.162';
    public $print_title = 'QCL - Grand Tambola';
    public $print_subtitle = 'Sep 2024';
    public $customerPayment, $modal_title, $modal_message, $change;
    public $showModal;
    public function mount(Order $order)
    {
        $this->order = $order;
        $this->showModal = false;
        $this->modal_title = __('order.Payment_Title');
        $this->printer_ip = $order->shop->printer_ip;
        $this->initialize();
        // __('order.Payment_Text')
        // __('order.Part_Chit_Title')
        // __('order.Part_Chit_Text')
        // __('order.Part_Chit_Warning_Title')
        // __('order.Part_Chit_Warning_Text')
        // __('order.Change_Title')
        // __('order.Change_Text')
    }
    public function initialize()
    {
        $this->customerPayment = $this->order->balance();
    }
    public function render()
    {
        return view('livewire.order-token-payment');
    }

    // #[On('item-added-to-order')]
    #[On('order-updated')]
    public function updateOrder($orderId)
    {
        if ($this->order->id == $orderId) {
            // $this->message = $orderId;
            $this->order = Order::find($this->order->id);
        }
        $this->initialize();
    }

    public function checkPayment()
    {
        if ($this->customerPayment == $this->order->balance()) {
            $this->modal_title = __('order.Payment_Title');
            $this->modal_message = __('order.Payment_Text');
            // } elseif ($this->customerPayment < $this->order->balance()) {
            //     $this->modal_title = __('order.Part_Chit_Title');
            //     $this->modal_message = __('order.Part_Chit_Text');
            //     $this->change = $this->customerPayment - $this->order->balance();
        } else {
            $this->modal_title = __('order.Change_Title');
            $this->modal_message = __('order.Change_Text');
            $this->change = $this->customerPayment - $this->order->balance();
        }
        $this->showModal = true;
    }

    public function payAndClose()
    {
        $this->order->payments()->create([
            'order_id' => $this->order->id,
            'user_id' => auth()->user()->id,
            'amount' => ($this->customerPayment >= $this->order->balance()) ? $this->order->balance() : $this->customerPayment,
        ]);
        if ($this->order->POS_number == null) $this->order->assignPOS();
        $this->order->state = 'closed';
        $this->order->bakeOrder();
        $this->order->save();
        $this->printTokens();
        return $this->redirect(('/tokenShop'));
    }
    public function printTokens()
    {

        foreach ($this->order->items() as $items) {
            $kitchen_printer_ip = $this->printer_ip;
            try {
                $connector = new NetworkPrintConnector($kitchen_printer_ip, 9100, 5);
                $printer = new Printer($connector);
                try {
                    // $this->print_POS_Header($kitchen_printer, $order, $heading = "Quetta Club Limited\n---------------------\nQCL - Kitchen KOT\n");
                    foreach ($items as $item) {

                        //Header
                        $printer->setTextSize(2, 2);
                        $printer->setEmphasis(true);
                        $printer->setJustification(Printer::JUSTIFY_CENTER);
                        $printer->text(str_repeat("-", 22) . "\n");

                        $printer->setFont(Printer::FONT_A); // change font
                        $printer->text($this->print_title . "\n");
                        $printer->text($this->print_subtitle . "\n");
                        $printer->setFont(Printer::FONT_A); // change font

                        $printer->text(str_repeat("-", 22) . "\n");
                        $printer->setJustification(Printer::JUSTIFY_LEFT);
                        $printer->setTextSize(1, 1);

                        $printer->setTextSize(1, 1);
                        $printer->text("POS Order Receipt: ");
                        $printer->setTextSize(1, 1);
                        $printer->text($this->order->POS_number . "\n");
                        $printer->setTextSize(1, 1);


                        $printer->text("Order Date: " . $this->order->created_at . "\n");
                        // $printer->text("Items:\n");
                        // $printer->text('- ' . $item->product->name . '(' . $item->product->price * $item->quantity . ')' . ' x ' . $item->quantity . "\n");
                        $printer->setEmphasis(false);
                        $printer->setJustification(Printer::JUSTIFY_CENTER);
                        $printer->text(str_repeat("-", 42) . "\n");

                        $printer->setJustification(Printer::JUSTIFY_LEFT);
                        $printer->setEmphasis(true);


                        $printer->setJustification(Printer::JUSTIFY_LEFT);
                        $printer->setTextSize(2, 2);
                        // $kitchen_printer->setTextSize(1, 1);
                        $printer->text($item->product->name);
                        // $printer->text("\n");
                        $printer->setJustification(Printer::JUSTIFY_CENTER);
                        $printer->text("\n");
                        $printer->text(" - " . $item->quantity . "\n");
                        $printer->text("\n");
                        $printer->text("\n");
                        $printer->text("\n");

                        // $printer->text("Rate:(" . $item->product->price . ")");
                        // $printer->setJustification(Printer::JUSTIFY_CENTER);
                        //$kitchen_printer->setTextSize(2,2);
                        //$kitchen_printer->text("\n");
                        $printer->setEmphasis(false);

                        // $kitchen_printer->setTextSize(2, 2);
                        // $kitchen_printer->setEmphasis(true);
                        // $kitchen_printer->text("Amount: " . (int) $item->price . "\n");
                        $printer->setJustification(Printer::JUSTIFY_CENTER);
                        $printer->setTextSize(1, 1);
                        $printer->text(str_repeat("-", 42) . "\n");
                        $printer->cut();
                    }

                    // $this>print_POS_Footer($kitchen_printer, $order, false);
                } catch (Exception $e) {
                    logger($e->getMessage());
                } finally {
                    $printer->close();
                }
            } catch (Exception $e) {
                logger('Failed to connect to kitchen_printer: ' . $this->printer_ip . $e->getMessage());
                $this->err =   'Failed to connect to kitchen_printer: ' . $this->printer_ip . $e->getMessage();
            }
        }
    }
}
