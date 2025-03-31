<?php

namespace App\Livewire;

use App\Http\Controllers\OrderHistoryController;
use App\Models\Order;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\On;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class OrderTokenPayment extends Component
{
    public $order, $err;
    public $printer_ip = '192.168.0.162';
    public $token_print_title = 'QCL - Grand Tambola';
    public $token_print_subtitle = 'Sep 2024';
    public $customerPayment, $modal_title, $modal_message, $change;
    public $showModal;
    public function mount(Order $order)
    {
        $this->token_print_title = Setting::firstOrCreate(['key' => 'token_print_title'], ['value' => 'QCL - Grand Tambola'])['value'];
        $this->token_print_subtitle = Setting::firstOrCreate(['key' => 'token_print_subtitle'], ['value' => 'Sep 2024'])['value'];
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
        if ($this->order->state == 'closed') return redirect('/orders/' . $this->order->id);

        // if (true) {
        //     $order = Order::where('POS_number', 'LIKE', '%29-03-2025')->first();
        //     $order->history = \Illuminate\Database\Eloquent\Collection::make([
        //         new \App\Models\OrderHistory([
        //             'id' => 12,
        //             'order_id' => 2011,
        //             'user_id' => 1,
        //             'action_type' => "pos-assigned",
        //             'description' => "POS Number 1036-29-03-2025 assigned by Zia Khan at 29-Mar-25 20:58",
        //             'created_at' => "2025-03-29 20:58:26",
        //             'updated_at' => "2025-03-29 20:58:26",
        //         ]),
        //     ]);
        //     dd($order->history);
        // }
        if ($this->order->history->where('action_type', 'kot-printed')->count()) {
            session()->flash('message', 'Order is already printed');
            Log::warning('Order is already printed');
            return redirect('/tokenShop');
        }



        $this->order->payments()->create([
            'order_id' => $this->order->id,
            'user_id' => auth()->user()->id,
            'amount' => ($this->customerPayment >= $this->order->balance()) ? $this->order->balance() : $this->customerPayment,
        ]);
        // dd($this->order);
        if ($this->order->POS_number == null) $this->order->assignPOS();
        $this->order->state = 'closed';
        $this->order->bakeOrder();
        $this->order->save();
        $this->printTokens();

        return
            $this->redirect(('/tokenShop'));
        // $this->err;
    }
    public function printTokens()
    {
        Log::info('Printing order tokens for POS # ' . $this->order->POS_number);
        $kitchen_printer_ip = $this->order->shop->printer_ip;;
        Log::info('Printer IP: ' . $kitchen_printer_ip);
        try {
            $connector = new NetworkPrintConnector($kitchen_printer_ip, 9100, 5);
            $printer = new Printer($connector);
            try {
                // $this->print_POS_Header($kitchen_printer, $order, $heading = "Quetta Club Limited\n---------------------\nQCL - Kitchen KOT\n");
                //     foreach ($items as $item) {
                foreach ($this->order->items as $item) {

                    //Header
                    $printer->setTextSize(2, 2);
                    $printer->setEmphasis(true);
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text(str_repeat("-", 22) . "\n");

                    $printer->setFont(Printer::FONT_A); // change font
                    $printer->text($this->token_print_title . "\n");
                    $printer->text($this->token_print_subtitle . "\n");
                    $printer->setFont(Printer::FONT_A); // change font

                    $printer->text(str_repeat("-", 22) . "\n");
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->setTextSize(1, 1);

                    $printer->setTextSize(1, 1);
                    $printer->text("POS Order Receipt: ");
                    $printer->text($this->order->POS_number . "\n");

                    $printer->setTextSize(1, 1);
                    $printer->text("Cashier: ");
                    $printer->text($this->order->user->getFullName()  . "\n");


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
                    $printer->text($item->product->name ?? $item->product_name);
                    // $printer->text("\n");
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("\n");
                    $printer->text("QTY - " . $item->quantity . "\n");
                    $printer->text("\n");
                    $printer->setTextSize(1, 1);
                    $printer->text("Price - " . $item->product_rate . "\n");
                    $printer->text("\n");
                    $printer->text("\n");
                    // $shop_printer->text("Amount: " . number_format((int) $item?->price ?? $item->product_rate) . "\n");

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
                $orderItemString = $this->order->items->map(function ($item) {
                    return  $item->product_name . ':' . $item->quantity;
                })->implode(', ');
                $orderHistoryController = new OrderHistoryController();
                $orderHistoryController->store($request = null, orderId: $this->order->id, actionType: 'kot-printed', printerIdentifier: $kitchen_printer_ip, itemName: $orderItemString);
                // OrderHistoryController::store($this->order->id, 'token-printed');
                Log::info('Order Token  Printed' . $orderItemString);
            }
        } catch (Exception $e) {
            $orderItemString = $this->order->items->map(function ($item) {
                return  $item->product_name . ':' . $item->quantity;
            })->implode(', ');
            $orderHistoryController = new OrderHistoryController();
            $orderHistoryController->store($request = null, orderId: $this->order->id, actionType: 'kot-failed', printerIdentifier: $kitchen_printer_ip, itemName: $orderItemString);
            // OrderHistoryController::store($this->order->id, 'token-printed');

            Log::warning('Order Token NOT Printed' . $orderItemString);

            logger('Failed to connect to kitchen_printer: ' . $this->printer_ip . $e->getMessage());
            $this->err =   'Failed to connect to kitchen_printer: ' . $this->printer_ip . $e->getMessage();
        }
    }
}
