<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Traits\ListOf;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use App\Models\Shop;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Discount;
use App\Models\OrderItem;
use App\Models\Product;
// use App\Models\Category;
use AliBayat\LaravelCategorizable\Category;
use App\Http\Requests\OrderNewRequest;
use Illuminate\Log\Logger;
use App\Jobs\PrintOrderTokensJob;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Exception;
use Mike42\Escpos\EscposImage;

// use Mike42\ecspo;EscposImage

// use PDF;

class OrderController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return Order::class;
    }
    public function index(Request $request)
    {
        $request = $request->collect()->filter(function ($value) {
            return null !== $value;
        })->toArray();
        $request = collect($request);
        // dd($request);
        if (auth()->user()->type == 'admin') {
            $orders = Order::query();
        } else {
            $u = User::with('shops')->find(auth()->id());

            $shops = $u->shops()->pluck('shops.id')->toArray();
            // dd($shops);
            $orders = Order::whereIn('shop_id', $shops);
        }
        // FILTERS
        // POS Number	
        // dd($request->has('pos_number'), $request);
        if ($request->has('pos_number') && $request['pos_number'] != null) {
            $orders = $orders->where('pos_number', $request['pos_number']);
        }
        // Customer Name	
        if ($request->has('customer_name') && $request['customer_name'] != null) {
            $orders = $orders->whereHas('customer', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request['customer_name'] . '%');
            });
        }
        // Type	
        if ($request->has('type') && $request['type'] != null) {
            // dd($request->type);
            $orders = $orders->where('type', $request['type']);
        }
        // Table #	
        if ($request->has('table_number') && $request['table_number'] != null) {
            $orders = $orders->where('table_number', $request['table_number']);
        }
        // Waiter Name	
        if ($request->has('waiter_name') && $request['waiter_name'] != null) {
            $orders = $orders->where('waiter_name', 'LIKE', '%' . $request['waiter_name'] . '%');
        }
        // Shop Name	
        if ($request->has('shop_name') && $request['shop_name'] != null) {
            $orders = $orders->whereHas('shop', function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request['shop_name'] . '%');
            });
        }
        // Total	

        // Discount	
        // Net Amount	
        // Cash	
        // Chit	

        // Taken By	
        // if ($request->has('Taken_By')) {

        // Closed By	
        // if ($request->has('Closed_By')) {

        // Status
        // if ($request->has('Status')) {

        // if ($request->has('state')) {
        //     $filters = array_intersect(['preparing', 'served', 'closed', 'wastage'], $request->state);
        //     $orders = $orders->whereIn('state', $filters);
        // }

        // if ($request->has('type')) {
        //     $filters = array_intersect(['dine-in', 'take-away', 'delivery'], $request->type);
        //     $orders = $orders->whereIn('type', $filters);
        // }
        $today = now()->startOfDay();
        if ($request->has('all') && $request['all'] == '1') {
            //no date filtering....
        } elseif ($request->has('start_date') && $request->has('end_date')) {
            $orders = $orders->whereBetween('created_at', [$request['start_date'], $request['end_date'] . ' 23:59:59']);
        } elseif ($request->has('start_date')) {
            $orders = $orders->whereDate('created_at', '>=', $request['start_date']);
        } elseif ($request->count() > 0) {
        } else {
            $orders = $orders->whereDate('created_at', $today);
        }

        $unpaid = $request->has('unpaid') && $request['unpaid'] == '1';
        // $chit = $request->has('chit') && $request->chit == '1';
        // $discounted = $request->has('discounted') && $request->discounted == '1';

        if ($unpaid) {
            $orders = $orders->whereDoesntHave('payments');
        }
        // if ($chit) {
        //     // $orders = $orders->where(function ($query) {
        //     //     $query->where('balance', '>', 0);
        //     // });
        // }
        // if ($discounted) {
        //     // $orders = $orders->whereHas('discounts', 'hasAny');
        // }

        $orders = $orders->with(['items', 'payments', 'customer', 'shop'])->orderBy('created_at', 'desc')->get(); //->paginate(25);

        $total = $orders->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();
        $totalTotal = $orders->map(function ($i) {
            return $i->total();
        })->sum();
        $totalDiscountAmount = $orders->map(function ($i) {
            return $i->discountAmount();
        })->sum();
        $totalNetAmount = $orders->map(function ($i) {
            return $i->discountedTotal();
        })->sum();
        $totalReceivedAmount = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();
        $totalChitAmount = $orders->map(function ($i) {
            return $i->balance();
        })->sum();
        return view('orders.index', compact('orders', 'total', 'receivedAmount', 'totalTotal', 'totalDiscountAmount', 'totalNetAmount', 'totalReceivedAmount', 'totalChitAmount'));
    }

    public function edit(Order $order)
    {
        if ($order->state == 'closed') {
            return back()->with('message', 'Order is already closed');
        }
        $order = $order->load(['items.product', 'payments', 'customer', 'shop']);
        $users = User::all();
        $discounts = Discount::orderBy('type')->get();
        $shops = Shop::all();
        $customers = Customer::all();
        $products = Product::all();
        return view('orders.edit', compact('order', 'shops', 'customers', 'users', 'discounts', 'products'));
    }

    public function makeNew(OrderNewRequest $request)
    {
        $request->merge(['user_id' => auth()->id()]);
        $order = Order::create($request->all());

        return redirect()->route('orders.edit', $order)->with('success', 'Order created successfully');
    }
    public function newEdit()
    {
        $order = Order::create(['user_id' => auth()->id()]);
        $users = User::all();
        $discounts = Discount::orderBy('type')->get();
        $shops = Shop::all();
        $customers = Customer::all();
        $products = Product::all();
        return view('orders.edit', compact('order', 'shops', 'customers', 'users', 'discounts', 'products'));
    }


    public function update(Request $request, Order $order)
    {
        // dd($request);
        //     "shop_id" => "2"
        //   "customer_id" => "88"
        //   "table_number" => "446"
        //   "waiter_name" => "Maribel Bogan"
        //   "type" => "take-away"
        //   "user_id" => "2"
        //   "state" => "preparing"
        $validatedData = $request->validate([
            'shop_id' => 'nullable|exists:shops,id',
            'customer_id' => 'nullable|exists:customers,id',
            'table_number' => 'nullable|string',
            'waiter_name' => 'nullable|string',
            'type' => 'nullable|in:dine-in,take-away,delivery',
            'notes' => 'nullable|string',
            // 'user_id' => 'required|exists:users,id',
            // 'state' => 'nullable|in:preparing,served,closed,wastage',
        ]);

        $validatedData['user_id'] = auth()->user()->id;

        $order->update($validatedData);

        return redirect()->route('orders.edit', $order)->with('success', 'Order updated successfully');
    }

    public function addPayment(Request $request, Order $order)
    {
        // logger("addPayment:", $request->all());
        // dd($request->all(), $order);
        // dd('$validatedData');
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0',
            // 'order_id' => 'required|exists:orders,id',
            // 'user_id' => 'required|exists:users,id',
        ]);
        $validatedData['order_id'] = $order->id;
        $validatedData['user_id'] = $request->user()->id;
        // dd('$validatedData');
        $order->payments()->create($validatedData);


        if ($order->stateLabel() == __('order.Not_Paid')) {
            // $routeString = 'orders.edit';
            $message = 'Payment added successfully';
        } elseif ($order->stateLabel() == __('order.Partial')) {
            // $routeString = 'orders.edit';
            $message = 'Payment added successfully';
        } elseif ($order->stateLabel() == __('order.Paid')) {
            // $routeString = 'orders.show';
            $message = 'Payment added successfully';
            // $order->state = 'closed';
            // $order->save();
        } elseif ($order->stateLabel() == __('order.Change')) {
            $customerName = $order->customer ? $order->customer->name : 'unknown';
            $message = 'Payment added successfully & Change attributed to ' . $customerName;
        }
        $routeString = 'orders.show';
        $order->state = 'closed';
        $order->save();


        return redirect()->route($routeString, $order)->with('success', $message);
    }

    public function addItem(Request $request, Order $order)
    {
        if ($order->state == 'closed') {
            return back()->with('message', 'Order is already closed');
        }
        // dd($request->all());
        $product = Product::find($request->item);

        $validatedData = $request->validate([
            'item' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $validatedData['order_id'] = $order->id;
        $validatedData['user_id'] = $request->user()->id;

        if ($existingItem = $order->items()->where('product_id', $product->id)->first()) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $request->quantity,
                'price' => $product->price * ($existingItem->quantity + $request->quantity),
            ]);
        } else {
            $validatedData['product_id'] = $product->id;
            $validatedData['price'] = $product->price * $request->quantity;
            $order->items()->create($validatedData);
        }
        // $order->items()->create($validatedData);

        return redirect()->route('orders.edit', $order)->with('success', 'Product added to order successfully');
    }
    public function destroy(Order $order)
    {
        dd($order);
        $order->delete();
        return redirect()->route('orders.index', $order)->with('success', 'order deleted successfully');
    }
    public function destroyItem(Order $order, OrderItem $item)
    {
        if ($order->state == 'closed') {
            return back()->with('message', 'Order is already closed');
        }
        $item->delete();

        return redirect()->route('orders.edit', $order)->with('success', 'Product deleted from order successfully');
    }

    public function destroyPayment(Order $order, Payment $payment)
    {
        $payment->delete();

        return redirect()->route('orders.edit', $order)->with('success', 'Payment deleted successfully');
    }

    public function updateDiscounts(Order $order, Request $request)
    {
        if ($order->state == 'closed') {
            return back()->with('message', 'Order is already closed');
        }
        $prevDiscounts = $order->discounts()->get();
        $validatedData = $request->validate([
            'discountsToAdd' => 'nullable|array',
            'discountsToAdd.*' => 'nullable|exists:discounts,id',
        ]);
        // dd($validatedData);
        $discountsToAdd = $validatedData['discountsToAdd'] ?? [];
        $order->discounts()->sync($discountsToAdd);
        activity('order-discount')
            ->causedBy($request->user())
            ->performedOn($order)
            ->withProperties(['old' => $prevDiscounts, 'attributes' => $order->discounts()->get()])
            ->log('edited');
        return redirect()->route('orders.edit', $order)->with('success', 'Discounts updated successfully');
    }
    public function show(Order $order)
    {

        // get previous user id
        $previous = Order::where('id', '<', $order->id)
            ->where('user_id', $order->user_id)
            ->max('id');


        // get next user id
        $next = Order::where('id', '>', $order->id)
            ->where('user_id', $order->user_id)
            ->min('id');
        // $currentKey = array_search($order->id, $orders);
        // $next = $currentKey === false ? null : $orders[($currentKey + 1) % count($orders)];
        // $previous = $currentKey === false ? null : $orders[($currentKey - 1 + count($orders)) % count($orders)];
        return view('orders.show', [
            'order' => $order->load([
                'items.product',
                'payments',
                'customer',
                'shop'
            ]),
            'next' => $next,
            'previous' => $previous,
        ]);
    }
    public function store(OrderStoreRequest $request)
    {
        // logger($request);
        $order = Order::create([
            'customer_id' => $request->customer_id,
            'user_id' => $request->user()->id,
            'shop_id' => $request->shop_id,
            'table_number' => $request->table_number,
            'waiter_name' => $request->waiter_name,
            'type' => $request->order_type,
            'notes' => $request->notes,
        ]);

        $cart = $request->user()->cart()->get();
        foreach ($cart as $item) {
            $order->items()->create([
                'price' => $item->price * $item->pivot->quantity,
                'quantity' => $item->pivot->quantity,
                'product_id' => $item->id,
            ]);
            // $item->quantity = $item->quantity - $item->pivot->quantity;
            // $item->save();
        }
        $request->user()->cart()->detach();
        if ($request->amount) {
            $order->payments()->create([
                'amount' => $request->amount,
                'user_id' => $request->user()->id,
            ]);
        }
        if ($request->discountsToAdd) {
            $order->discounts()->sync($request->discountsToAdd);
        }
        return ['message' => 'success', 'order' => $order];
    }
    public function print($id)
    {
        $order = Order::with(['items.product', 'payments', 'customer', 'shop'])
            ->findOrFail($id);
        $html = 'Order ID: ' . $order->id . "\n";
        $html .= 'Customer: ' . $order->customer->name . "\n";
        $html .= 'Date: ' . $order->created_at . "\n";
        $html .= 'Notes: ' . $order->notes . "\n";
        $html .= 'Items: ' . "\n";
        foreach ($order->items as $item) {
            if ($item->pivot) {
                $html .= '- ' . $item->product->name . ' x ' . $item->pivot->quantity . "\n";
            }
        }
        // $html .= 'Total: ' . $order->total() . "\n";
        // -$html .= 'Received: ' . $order->receivedAmount() . "\n";
        return response($html, 200)
            ->header('Content-Type', 'text/plain');
    }
    public function printPreview($id)
    {
        $order = Order::with(['items.product', 'payments', 'customer', 'shop'])
            ->findOrFail($id);
        return View('pdf.order', compact('order'));
    }
    public function printPdf($id)
    {
        $order = Order::with(['items.product', 'payments', 'customer', 'shop'])
            ->findOrFail($id);
        $orderStatus = $this->getOrderStatus($order);

        $pdf = Pdf::loadView('pdf.order80mm2', compact('order', 'orderStatus'));
        $pdf->set_option('dpi', 72);
        $pdf->setPaper([0, 0, 204, 650], 'portrait'); // 80mm thermal paper
        return $pdf->download('order_' . $order->id . '.pdf');
    }



    public function printTokens($id)
    {
        // Logger(['printTokens func:', $id]);;;




        // Use laravel queue to handle printing
        // This will prevent blocking the HTTP request
        // PrintOrderTokensJob::dispatch($id);
        // dispatch(PrintOrderTokensJob::makeJob($id));


        return $this->downloadOrderTokensPDF($id);
    }
    /**
     * Download a PDF of the order with the given ID
     *
     * @param int $id The ID of the order to download
     * @return \Illuminate\Http\Response The PDF file
     */
    public function downloadOrderPDF($id)
    {
        // Logger(['downloadOrderPDF:', $id]);;;
        $order = Order::with(['items.product', 'payments', 'customer', 'shop'])
            ->findOrFail($id);
        $orderStatus = $this->getOrderStatus($order);
        $pdf = Pdf::loadView('pdf.order80mm2', compact('order', 'orderStatus'));
        $pdf->set_option('dpi', 72);
        $pdf->setPaper([0, 0, 204, 650], 'portrait'); // 80mm thermal paper
        return $pdf->download('order_' . $order->id . '.pdf');
    }
    public function downloadOrderTokensPDF($id)
    {
        // Logger(['downloadOrderTokensPDF:', $id]);;;
        $order = Order::with(['items.product', 'payments', 'customer', 'shop'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.ordertokens80mm', compact('order'));
        $pdf->set_option('dpi', 72);
        $pdf->setPaper([0, 0, 204, 300], 'portrait'); // 80mm thermal paper
        return $pdf->download('order_' . $order->id . '.pdf');
    }

    public function getOrderStatus(Order $order)
    {
        if ($order->state == 'closed') {
            $orderStatus = '';
            switch ($label = $order->stateLabel()) {
                case __('order.Not_Paid'):
                    $orderStatus = 'UNPAID';
                    break;
                case __('order.Partial'):
                    $orderStatus = 'Part-Chit';
                    break;
                case __('order.Paid'):
                    $orderStatus = 'PAID';
                    break;
                case __('order.Change'):
                    $orderStatus = 'Change';
                    break;
            }
            return $orderStatus;
        } else return '|';
    }

    public function printToPOS(Order $order)
    {
        try {
            // $this->print_POS_Category_wise_Token($order);
            $this->print_POS_Order($order);
        } catch (Exception $e) {
            logger('Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed xxx: ' . $e->getMessage());
        }
        return redirect()->back()->with('success', 'Order printed successfully');
    }
    public function printToPOSQT(Order $order)
    {
        try {
            $this->print_POS_Category_wise_Token($order);
            // $this->print_POS_Order($order);
        } catch (Exception $e) {
            logger('Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed xxx: ' . $e->getMessage());
        }
        return redirect()->back()->with('success', 'Order printed successfully');
    }

    private function print_POS_Token(Printer $printer, Order $order, OrderItem $item)
    {
        // logger(public_path('images/logo_blk.jpg'));
        // $printer->graphics(EscposImage::load(public_path('images/logo_blk.jpg'), false));

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("\n" . str_repeat("-", 42) . "\n");
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->setFont(Printer::FONT_A); // change font
        $printer->text("Quetta Club Limited\n");
        // $printer->setFont(Printer::FONT_B); // change font
        // $printer->text("Quetta Club Limited\n");
        // $printer->setFont(Printer::FONT_C); // change font
        // $printer->text("Quetta Club Limited\n");
        $printer->setFont(Printer::FONT_A); // change font
        $printer->text("Chand Raat Festival\n");
        $printer->text("2024\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text(str_repeat("-", 42) . "\n");
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->setTextSize(1, 1);
        $printer->text("POS Order Token\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text($order->POS_number . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);


        $printer->setJustification(Printer::JUSTIFY_LEFT);

        // $printer->dataHeader('POS ' . $order->POS_number);
        // $printer->setFooter("User: " . $order->user ? $order->user->getFullName() : "Guest" . "  Shop: " . $order->shop ? $order->shop->name : "Unknown");
        $printer->text("Customer: ");

        if ($order->customer) {
            $printer->text($order->customer->name . "\n");
        } else {
            $printer->text("Walk in Customer\n");
        }

        $printer->text("Date: " . $order->created_at . "\n");
        // $printer->text("Items:\n");
        // $printer->text('- ' . $item->product->name . '(' . $item->product->price * $item->quantity . ')' . ' x ' . $item->quantity . "\n");
        $printer->setEmphasis(true);
        $printer->text(str_repeat("-", 42) . "\n");

        $printer->setTextSize(2, 2);
        $printer->text($item->product->name);
        $printer->setTextSize(1, 1);
        $printer->text("\n @ (" . $item->product->price . ")\n");
        $printer->text("\n");
        $printer->setTextSize(3, 3);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("QTY: " . $item->quantity . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text(str_repeat("-", 42) . "\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setTextSize(2, 2);
        // $printer->text("Payments:");
        // foreach ($order->payments as $payment) {
        //     $printer->text('- ' . $payment->method . ' ' . $payment->amount . "\n");
        // }
        $printer->text("Total: " . $order->total() . "\n");

        $printer->setTextSize(1, 1);
        if ($order->notes) {
            $printer->text("Notes: " . $order->notes  . "\n");
        }
        $printer->text("Cashier: " . $order->user->getFullName()  . "\n");
        $printer->text("Shop: " . $order->shop ? $order->shop->name : "Unknown");
        $printer->text("\nOn: "  . "");
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text(date('Y-m-d H:i:s') . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);

        $printer->text("\n \n");
        $printer->cut();
    }
    private function print_POS_Header(Printer $printer, Order $order, String $heading = "Quetta Club Limited\n---------------------\n*** Customer Bill ***\n")
    {
        $printer->setTextSize(2, 2);
        $printer->setEmphasis(true);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(str_repeat("-", 22) . "\n");

        $printer->setFont(Printer::FONT_A); // change font
        $printer->text($heading);
        // $printer->setFont(Printer::FONT_B); // change font
        // $printer->text("Quetta Club Limited\n");
        // $printer->setFont(Printer::FONT_C); // change font
        // $printer->text("Quetta Club Limited\n");
        $printer->setFont(Printer::FONT_A); // change font
        // $printer->text("Chand Raat Festival\n");
        // $printer->text("2024\n");

        $printer->text(str_repeat("-", 22) . "\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setTextSize(1, 1);
        if ($order->type) {
            //$printer->setTextSize(2, 2);
            $printer->text("Order Type:");
            $printer->text($order->type . "\n");
        }
        if ($order->type == 'dine-in' && $order->table_number) {
            //$printer->setTextSize(2, 2);
            $printer->text("Table # ");
            $printer->text($order->table_number . "\n");
        }
        if ($order->waiter_name) {
            //$printer->setTextSize(2, 2);
            $printer->text("Waiter: ");
            $printer->text($order->waiter_name  . "\n");
        }
        if ($order->notes) {
            $printer->text("Notes: " . $order->notes  . "\n");
        }

        //$printer->setTextSize(2, 2);
        $printer->text("Order for: ");
        $printer->text($order->shop->name . "\n");






        //$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        //$printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setTextSize(1, 1);
        $printer->text("POS Order Receipt: ");
        $printer->setTextSize(1, 1);
        $printer->text($order->POS_number . "\n");
        $printer->setTextSize(1, 1);

        // $printer->dataHeader('POS ' . $order->POS_number);
        // $printer->setFooter("User: " . $order->user ? $order->user->getFullName() : "Guest" . "  Shop: " . $order->shop ? $order->shop->name : "Unknown");
        $printer->text("Customer: ");

        if ($order->customer) {
            $printer->text($order->customer->name . "\n");
        } else {
            $printer->text("Walk in Customer\n");
        }


        $printer->text("Order Date: " . $order->created_at . "\n");
        // $printer->text("Items:\n");
        // $printer->text('- ' . $item->product->name . '(' . $item->product->price * $item->quantity . ')' . ' x ' . $item->quantity . "\n");
        $printer->setEmphasis(false);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(str_repeat("-", 42) . "\n");
    }
    private function print_POS_Footer(Printer $printer, Order $order, $showTotal = true)
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(1, 1);
        $printer->text(str_repeat("-", 42) . "\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        // $printer->text("Payments:");
        // foreach ($order->payments as $payment) {
        //     $printer->text('- ' . $payment->method . ' ' . $payment->amount . "\n");
        // }
        if ($showTotal) {
            $printer->setTextSize(2, 2);
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("Total: " . $order->total() . "\n");
        }
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setTextSize(1, 1);
        $printer->text("Order by: " . $order->user->getFullName()  . "\n");
        if (count($order->payments)) {
            $printer->text("Closed by: " . $order->payments[0]->user->getFullName()  . "\n");
        }
        // $printer->text("Shop: " . $order->shop ? $order->shop->name : "Unknown");
        $printer->text("\nPrint Date: "  . "");
        // $printer->setJustification(Printer::JUSTIFY_CENTER);
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
            $printer->text("E-mail: quettaclublimited@gmail.com");
        }
        $printer->text("\n \n");
        $printer->cut();
    }
    private function print_POS_Category_wise_Token(Order $order)
    {
        // logger('printing Cat-tokens job started');

        $items_by_category = $order->items->groupBy(function ($item) {
            return $item->product->categories[0]->kitchen_printer_ip;
        });
        // logger($items_by_category);
        foreach ($items_by_category as $ip => $items) {
            // logger('$ip');
            // logger($ip);
            // logger($items);
            $kitchen_printer_ip = $ip ?? config('settings.default_printer_ip');
            // logger('$kitchen_printer_ip');
            // logger($kitchen_printer_ip);
            try {
                $connector = new NetworkPrintConnector($kitchen_printer_ip, 9100, 5);
                $kitchen_printer = new Printer($connector);
                try {
                    $this->print_POS_Header($kitchen_printer, $order, $heading = "Quetta Club Limited\n---------------------\nQCL - Kitchen KOT\n");
                    foreach ($items as $item) {

                        $kitchen_printer->setJustification(Printer::JUSTIFY_LEFT);
                        $kitchen_printer->setEmphasis(true);
                        //$kitchen_printer->setTextSize(2, 2);
                        // $kitchen_printer->setTextSize(1, 1);
                        $kitchen_printer->text($item->product->name);
                        $kitchen_printer->text("\n");
                        $kitchen_printer->setJustification(Printer::JUSTIFY_RIGHT);
                        $kitchen_printer->text("Rate:(" . $item->product->price . ")");
                        $kitchen_printer->text("\n");

                        $kitchen_printer->setJustification(Printer::JUSTIFY_CENTER);
                        //$kitchen_printer->setTextSize(2,2);
                        $kitchen_printer->text("QTY: " . $item->quantity . "\n");
                        //$kitchen_printer->text("\n");
                        $kitchen_printer->setEmphasis(false);

                        // $kitchen_printer->setTextSize(2, 2);
                        // $kitchen_printer->setEmphasis(true);
                        // $kitchen_printer->text("Amount: " . (int) $item->price . "\n");
                        $kitchen_printer->setJustification(Printer::JUSTIFY_CENTER);
                        $kitchen_printer->setTextSize(1, 1);
                        $kitchen_printer->text(str_repeat("-", 42) . "\n");
                    }
                    $kitchen_printer->setEmphasis(true);
                    $kitchen_printer->setJustification(Printer::JUSTIFY_CENTER);
                    $kitchen_printer->setTextSize(1, 1);
                    $kitchen_printer->text("\nTotal Items:" . count($items) . "\n");
                    $kitchen_printer->setEmphasis(false);
                    $kitchen_printer->setJustification(Printer::JUSTIFY_LEFT);
                    $this->print_POS_Footer($kitchen_printer, $order, false);
                } catch (Exception $e) {
                    logger($e->getMessage());
                } finally {
                    $kitchen_printer->close();
                }
            } catch (Exception $e) {
                logger('Failed to connect to kitchen_printer: ' . $kitchen_printer_ip . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to connect to kitchen_printer: ' . $kitchen_printer_ip . $e->getMessage());
            }
        }
    }
    private function print_POS_Order(Order $order)
    {
        $shop_printer_ip = $order->shop->printer_ip ?? config('settings.default_printer_ip');
        try {
            $connector = new NetworkPrintConnector($shop_printer_ip, 9100, 5);
            $shop_printer = new Printer($connector);
            try {
                // ... Print stuff

                $this->print_POS_Header($shop_printer, $order);
                // logger($order);
                // logger($order->items);
                foreach ($order->items as $item) {
                    $shop_printer->setJustification(Printer::JUSTIFY_LEFT);
                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->text($item->product->name);
                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->text("\n Rate(" . $item->product->price . ")");
                    $shop_printer->text("\n");
                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->setJustification(Printer::JUSTIFY_CENTER);
                    $shop_printer->text("QTY: " . $item->quantity . "\n");
                    //$shop_printer->text("\n");
                    $shop_printer->setJustification(Printer::JUSTIFY_RIGHT);
                    //$shop_printer->setTextSize(2, 2);
                    $shop_printer->setEmphasis(true);
                    $shop_printer->text("Amount: " . (int) $item->price . "\n");

                    $shop_printer->setEmphasis(false);

                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->setJustification(Printer::JUSTIFY_CENTER);
                    $shop_printer->text(str_repeat("-", 42) . "\n");
                }

                $this->print_POS_Footer($shop_printer, $order);
            } catch (Exception $e) {
                logger($e->getMessage());
            } finally {
                $shop_printer->close();
            }
        } catch (Exception $e) {
            logger('Failed to connect to printer: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to connect to printer: ' . $e->getMessage());
        }
    }
}
