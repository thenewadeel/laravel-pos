<?php

namespace App\Jobs;

use App\Http\Controllers\OrderHistoryController;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class PrintToPOS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 20;
    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 6;
    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 3;
    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order, public User $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Log::alert($this->order);
        Log::debug("Executing PrintToPOS job for order  $this->order->id with POS # $this->order->POS_number");

        $this->print_POS_Order($this->order);

        Log::debug("{{{{{{{{----{{$this->order}}-----}}}}}}}}");
    }

    private function print_POS_Header(Printer $printer, String $heading = "Quetta Club Limited\n---------------------\n*** Customer Bill ***\n")
    {
        $printer->setTextSize(2, 2);
        $printer->setEmphasis(true);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(str_repeat("-", 22) . "\n");

        $printer->setFont(Printer::FONT_A); // change font
        $printer->text($heading);

        $printer->text(str_repeat("-", 16) . "\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setTextSize(1, 1);
        $printer->text("POS Order Receipt: ");
        $printer->text($this->order->POS_number . "\n");

        $printer->text("Customer: ");
        if ($this->order->customer) {
            $printer->text($this->order->customer->name . "\n");
        } else {
            $printer->text("Walk in Customer\n");
        }

        $printer->text("Order of: ");
        $printer->text($this->order->shop->name . "\n");

        if ($this->order->type) {
            $printer->text("Order Type:");
            $printer->text($this->order->type . "\n");
        }

        if ($this->order->type == 'dine-in' && $this->order->table_number) {
            $printer->setTextSize(2, 2);
            $printer->text("Table # ");
            $printer->text($this->order->table_number . "\n");
            $printer->setTextSize(1, 1);
        }
        if ($this->order->waiter_name) {
            $printer->text("Waiter: ");
            $printer->text($this->order->waiter_name  . "\n");
        }

        $printer->text("Order Date: " . $this->order->created_at . "\n");

        if ($this->order->notes) {
            $printer->text("Notes: " . $this->order->notes  . "\n");
        }
        $printer->setEmphasis(false);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(str_repeat("-", 42) . "\n");
    }
    private function print_POS_Footer(Printer $printer, $showTotal = true)
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(1, 1);
        $printer->text(str_repeat("-", 42) . "\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        if ($showTotal) {
            $printer->setTextSize(3, 3);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("G.Total: " . number_format((int) $this->order->discountedTotal(), 0) . "\n");

            $balance = $this->order->balance();

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);

            if ($this->order->state == 'closed') {
                if ($balance == 0) {
                    $printer->text("\nPAID\n");
                } else if ($balance > 0) {
                    $printer->text("\nCHIT\n");
                    $printer->text(number_format((int)$balance, 0)  . "\n");
                }
            } else {
                $printer->text("\nBalance: " . $balance  . "\n");
            }
        }
        $printer->setTextSize(1, 1);
        $printer->text(str_repeat("-", 42) . "\n");
        $printer->text("\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Order by: " . $this->order->user->getFullName()  . "\n");
        if (count($this->order->payments)) {
            $printer->text("Closed by: " . $this->order->payments[0]->user->getFullName()  . "\n");
        }
        $printer->text("\nPrint Date: "  . "");
        $printer->text(date('Y-m-d H:i:s') . "\n");
        $printer->setTextSize(1, 1);
        if ($showTotal) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(1, 1);
            $printer->text(str_repeat("-", 42) . "\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(1, 1);
            $printer->text("Address: Club Road, Quetta Cantt.\n");
            $printer->text("Contact: Pascom 36251, PTCL 081-2849676\n");
            $printer->text("http://www.facebook.com/quettaclublimited\n");
            $printer->text("www.quettaclub.org\n");
            $printer->text("E-mail: info@quettaclub.org");
        }
        $printer->text("\n \n");
        $printer->cut();
    }
    private function print_POS_Order()
    {
        $shop_printer_ip = $this->user->fav_printer_ip ??
            $this->order->shop->printer_ip ?? config('settings.default_printer_ip');



        try {
            $connector = new NetworkPrintConnector($shop_printer_ip, 9100, 5);
            $shop_printer = new Printer($connector);
            try {
                // ... Print stuff

                $this->print_POS_Header($shop_printer);
                // logger($order);
                // logger($order->items);
                foreach ($this->order->items as $item) {
                    $shop_printer->setJustification(Printer::JUSTIFY_LEFT);
                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->text($item->product->name ?? $item->prduct_name);
                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->text("\n Rate(" . $item->product->price ?? $item->prduct_rate . ")");
                    $shop_printer->text("\n");
                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->setJustification(Printer::JUSTIFY_CENTER);
                    $shop_printer->text("QTY: " . $item->quantity . "\n");
                    //$shop_printer->text("\n");
                    $shop_printer->setJustification(Printer::JUSTIFY_RIGHT);
                    //$shop_printer->setTextSize(2, 2);
                    $shop_printer->setEmphasis(true);
                    $shop_printer->text("Amount: " . number_format((int) $item->price) . "\n");

                    $shop_printer->setEmphasis(false);

                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->setJustification(Printer::JUSTIFY_CENTER);
                    $shop_printer->text(str_repeat("-", 42) . "\n");
                }
                //$shop_printer->text(str_repeat("-", 42) . "\n");


                if ($this->order->discounts->count() > 0) {

                    $shop_printer->setEmphasis(true);
                    $shop_printer->text("\n");
                    $shop_printer->setTextSize(2, 2);
                    $shop_printer->setJustification(Printer::JUSTIFY_RIGHT);
                    $shop_printer->text("Total: " . number_format((int) $this->order->total()) . "\n");


                    $shop_printer->setEmphasis(false);

                    $shop_printer->setJustification(Printer::JUSTIFY_CENTER);

                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->text(str_repeat("-", 42) . "\n");


                    $shop_printer->setJustification(Printer::JUSTIFY_CENTER);
                    $shop_printer->setEmphasis(true);
                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->text("Discounts: " . "\n");
                    $shop_printer->setEmphasis(false);

                    $shop_printer->setTextSize(1, 1);

                    foreach ($this->order->discounts as $discount) {
                        $shop_printer->setJustification(Printer::JUSTIFY_LEFT);

                        $shop_printer->text(
                            $discount->name . '- (' . (int)$discount->percentage . " %)\n"
                        );
                    }
                    $shop_printer->setJustification(Printer::JUSTIFY_RIGHT);
                    $shop_printer->text("\n");

                    $shop_printer->setEmphasis(true);
                    $shop_printer->setTextSize(1, 1);

                    $shop_printer->text("Amount: " . number_format((int) $this->order->discountAmount()) . "\n");
                    $shop_printer->setEmphasis(false);

                    $shop_printer->setTextSize(1, 1);
                }

                $this->print_POS_Footer($shop_printer);
            } catch (Exception $e) {
                Log::alert("Failed to connect to printer B {$this->user->fav_printer_ip}: " . $e->getMessage());
            } finally {
                $shop_printer->close();

                // Create order history
                $orderHistoryController = new OrderHistoryController();
                $orderHistoryController->store(
                    $request = null,
                    orderId: $this->order->id,
                    actionType: 'pos-print-printed',
                    printerIdentifier: $this->order->shop->printer_ip ?? config('settings.default_printer_ip')
                );
            }
        } catch (Exception $e) {
            Log::alert("Failed to connect to printer A {$this->user->fav_printer_ip}: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
