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
use App\Jobs\PrintToPOS;
use App\Models\Feedback;
use App\Models\OrderHistory;
use Doctrine\DBAL\Schema\View;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Exception;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\EscposImage;
use ZipArchive;

// use Mike42\ecspo;EscposImage

// use PDF;

class OrderController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return Order::class;
    }

    private function handleDateFilter(Request $request, $query)
    {
        Log::info('Starting handleDateFilter', ['request' => $request->all()]);
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        } else if ($start_date) {
            $query->whereDate('created_at', $start_date);
        } else {
            $query->whereDate('created_at', now()->startOfDay());
        }

        Log::info('Ending handleDateFilter', ['modifiedQuery' => $query->toSql(), 'modifiedQueryBindings' => $query->getBindings()]);

        return $query;
    }

    private function handleOrderFilters(Request $request, $orderQuery)
    {
        Log::info('Starting handleOrderFilters', ['request' => $request->all()]);

        // Shop Filter
        $shop_id = $request->query('shop_ids');
        if ($shop_id) {
            $orderQuery->whereIn('shop_id', $shop_id);
        }

        // POS Number
        $pos_number = $request->query('pos_number');
        if ($pos_number) {
            $orderQuery = $orderQuery->where('pos_number', $pos_number);
        }

        // Type
        $type = $request->query('type');
        if ($type) {
            $orderQuery = $orderQuery->where('type', $type);
        }
        // Table #
        $table_number = $request->query('table_number');
        if ($request->has('table_number') && $request['table_number'] != null) {
            $orderQuery = $orderQuery->where('table_number', $table_number);
        }

        // Waiter Name
        $waiter_name = $request->query('waiter_name');
        if ($waiter_name) {
            $orderQuery = $orderQuery->where('waiter_name', 'LIKE', '%' . $waiter_name . '%');
        }

        // Customer Name
        $customer_name = $request->query('customer_name');
        if ($customer_name) {
            $orderQuery = $orderQuery->whereHas('customer', function ($query) use ($customer_name) {
                $query->where('name', 'LIKE', '%' . $customer_name . '%');
            });
        }

        // Order Taker Name
        $order_taker = $request->query('order_taker');
        if ($order_taker) {
            $orderQuery = $orderQuery->whereHas('user', function ($query) use ($order_taker) {
                $query->where('first_name', 'LIKE', '%' . $order_taker . '%')->orWhere('last_name', 'LIKE', '%' . $order_taker . '%');
            });
        }

        Log::info('Ending handleOrderFilters', ['modifiedQuery' => $orderQuery->toSql(), 'modifiedQueryBindings' => $orderQuery->getBindings()]);

        return $orderQuery;
    }

    public function index(Request $request)
    {

        $start = microtime(true);

        $orders = Order::query();
        $all = $request->query('all');
        $unpaid = $request->query('unpaid');
        if ($all) {
            //     //no date filtering....
            $orders = $orders->where('state', '<>', 'closed');
        } elseif ($unpaid) {
            $orders = $orders->whereDoesntHave('payments');
        } else {
            $orders = $this->handleDateFilter($request, $orders);
        }

        // FILTERS
        $this->handleOrderFilters($request, orderQuery: $orders);

        // dd($request);
        if (auth()->user()->type == 'admin') {
            // $orders = Order::query();
        } else {
            $shops = User::with('shops')->find(auth()->id())->shops()->pluck('shops.id')->toArray();
            // dd($shops);
            $orders = $orders->whereIn('shop_id', $shops);
        }
        //->with(['payments', 'customer', 'shop'])
        $orders = $orders
            ->with(['payments', 'shop'])
            ->orderBy('created_at', 'desc')
            ->get(); //->paginate(25); //

        // Payment State [open,closed, paid, chit, part-chit]
        if ($request->has('payment_state') && $request['payment_state'] != null) {
            // dd($orders);
            // logger($orders->first());
            // logger('$orders Inside');
            // logger($request['payment_state']);
            if ($request['payment_state'] == 'open') {
                $orders = $orders->filter(function (Order $order) {
                    return $order->state == 'preparing' || $order->state == 'served' || $order->state == 'wastage';
                });
            } elseif ($request['payment_state'] == 'closed') {
                $orders = $orders->filter(function (Order $order) {
                    return $order->state == 'closed';
                });
            } elseif ($request['payment_state'] == 'paid') {
                $orders = $orders->filter(function (Order $order) {
                    return $order->state == 'closed' && $order->balance() == 0;
                });
            } elseif ($request['payment_state'] == 'chit') {
                $orders = $orders->filter(function (Order $order) {
                    return $order->state == 'closed' && $order->receivedAmount() == 0 && $order->balance() > 0;
                });
            } elseif ($request['payment_state'] == 'part-chit') {
                $orders = $orders->filter(function (Order $order) {
                    return $order->state == 'closed' && $order->receivedAmount() > 0 && $order->balance() > 0;
                });
            }
        }

        $end = microtime(true);
        logger('Execution time: ' . ($end - $start) . ' seconds');

        $totals = $orders
            ->map(function ($i) {
                return [
                    'total' => $i->total(),
                    'receivedAmount' => $i->receivedAmount(),
                    'discountAmount' => $i->discountAmount(),
                    'discountedTotal' => $i->discountedTotal(),
                    'balance' => $i->balance(),
                ];
            })
            ->toArray();
        $total = array_sum(array_column($totals, 'total'));
        $receivedAmount = array_sum(array_column($totals, 'receivedAmount'));
        $totalTotal = array_sum(array_column($totals, 'total'));
        $totalDiscountAmount = array_sum(array_column($totals, 'discountAmount'));
        $totalNetAmount = array_sum(array_column($totals, 'discountedTotal'));
        $totalReceivedAmount = array_sum(array_column($totals, 'receivedAmount'));
        $totalChitAmount = array_sum(array_column($totals, 'balance'));

        // $end = microtime(true);
        // logger('Mid Execution time: ' . ($end - $start) . ' seconds');

        // $total = $orders->map(function ($i) {
        //     return $i->total();
        // })->sum();
        // $receivedAmount = $orders->map(function ($i) {
        //     return $i->receivedAmount();
        // })->sum();
        // $totalTotal = $orders->map(function ($i) {
        //     return $i->total();
        // })->sum();
        // $totalDiscountAmount = $orders->map(function ($i) {
        //     return $i->discountAmount();
        // })->sum();
        // $totalNetAmount = $orders->map(function ($i) {
        //     return $i->discountedTotal();
        // })->sum();
        // $totalReceivedAmount = $orders->map(function ($i) {
        //     return $i->receivedAmount();
        // })->sum();
        // $totalChitAmount = $orders->map(function ($i) {
        //     return $i->balance();
        // })->sum();

        $end = microtime(true);
        logger('End Execution time: ' . ($end - $start) . ' seconds');
        // dd($orders->count());

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
        if (auth()->user()->type == 'admin') { //auth()->user()->type == 'cashier' ||
            $products = $shops->map(function ($shop) {
                return $shop->products();
            })->flatten();
        } else {
            $products = auth()->user()->shops->map(function ($shop) {
                return $shop->products();
            })->flatten();
            // dd($products);
            // $cats = auth()->user()->shops->map(function ($shop) {
            //     return $shop->categories;
            // })->flatten();
            // $products = $cats->map(function ($cat) {
            //     return $cat->entries(Product::class);
            // });
        }
        return view('orders.edit', compact('order', 'shops', 'customers', 'users', 'discounts', 'products'));
    }

    public function makeNew(OrderNewRequest $request)
    {
        // dd($request->all());
        if ($request->has('customer_id') && $request->customer_id == null) {
            // } else {
            $customer = Customer::firstOrCreate(
                [
                    "name" => $request->searchCustomer,
                    "membership_number" => 555,
                    // "address" => $request->customer_address,
                    // "email" => $request->customer_email,
                    // "user_id" => $request->customer_id
                ]
            );
            $request['customer_id'] = $customer->id;
        }
        $request->merge(['user_id' => auth()->id()]);
        $order = Order::create($request->all());

        // Create order history
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request = null, orderId: $order->id, actionType: 'created');

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
        dd('obsolete method. Needs developer inspection');
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

        // $validatedData['user_id'] = auth()->user()->id;

        $order->update($validatedData);

        // Create order history for creation
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request, $order->id, 'updated');

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
            $validatedData['quantity'] = $request->quantity;
            $validatedData['product_name'] = $product->name;
            $validatedData['product_rate'] = $product->price;
            $order->items()->create($validatedData);
        }
        // $order->items()->create($validatedData);

        // Create order history for creation
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request, $order->id, 'item-added', $orderItem = $order->items()->where('product_id', $product->id)->first());

        return redirect()->route('orders.edit', $order)->with('success', 'Product added to order successfully');
    }
    public function destroy(Order $order)
    {
        // dd($order);
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
    public function destroyAllDrafts()
    {
        Order::whereNull('POS_number')->delete();
        return redirect()->back()->with('success', 'All drafts deleted successfully');
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

        // histories
        $histories = OrderHistory::where('order_id', $order->id)->orderBy('created_at')->get();

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
            'histories' => $histories
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

        // Create order history for creation
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request, $order->id, 'created');

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
        // dd('printPreview', $id);
        $order = Order::with(['items.product', 'payments', 'customer', 'shop'])
            ->findOrFail($id);
        return View('pdf.order', compact('order'));
    }
    public function printPdf($id)
    {
        // dd('printPdf', $id);
        $order = Order::with(['items.product', 'payments', 'customer', 'shop'])
            ->findOrFail($id);
        $orderStatus = $this->getOrderStatus($order);

        $pdf = Pdf::loadView('pdf.order80mm2', compact('order', 'orderStatus'));
        $pdf->set_option('dpi', 72);
        $pdf->setPaper([0, 0, 204, 400 + 25 * $order->items->count()], 'portrait'); // 80mm thermal paper

        // Save a copy of generated pdf in storage
        $path = storage_path('app/public/order_pdfs');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $pdfPath = $path . '/' . $order->id . '.pdf';
        $pdf->save($pdfPath);

        // Create order history
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request = null, orderId: $order->id, actionType: 'pdf-generated', pdfFilePath: $pdfPath);

        return $pdf->download('order_' . $order->id . '.pdf');
    }

    public function printBulkPdf(Request $request, $orderIdsArray)
    {
        // dd($orderIdsArray);
        $orderIds =  explode(',', $orderIdsArray);
        $orders = Order::with(['items.product', 'payments', 'customer', 'shop'])
            ->whereIn('id', $orderIds)
            ->get();

        $zip = new ZipArchive();
        $zipName = 'orders-' . now()->format('YmdHis') . '.zip';
        $zipPath = storage_path('app/' . $zipName);
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($orders as $order) {
            $orderStatus = $this->getOrderStatus($order);

            $pdf = Pdf::loadView('pdf.order80mm2', compact('order', 'orderStatus'));
            $pdf->set_option('dpi', 72);
            $pdf->setPaper([0, 0, 204, 400 + 25 * $order->items->count()], 'portrait'); // 80mm thermal paper
            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $order->POS_number);
            if (empty($filename)) {
                $filename = $order->id;
            }
            $pdf->save(storage_path('app/' . $filename . '.pdf'));
            $zip->addFile(storage_path('app/' . $filename . '.pdf'), $filename . '.pdf');
        }

        $zip->close();
        return response()->download($zipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ]);
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
        $pdf->setPaper([0, 0, 204, 400 + 35 * $order->items->count()], 'portrait'); // 80mm thermal paper
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
        return $order->stateLabel();
        // if ($order->state == 'closed') {
        // $orderStatus = '';
        // switch ($label = $order->stateLabel()) {
        //     case __('order.Not_Paid'):
        //         $orderStatus = 'CHIT';
        //         break;
        //     case __('order.Partial'):
        //         $orderStatus = 'Part-Chit';
        //         break;
        //     case __('order.Paid'):
        //         $orderStatus = 'PAID';
        //         break;
        //     case __('order.Change'):
        //         $orderStatus = 'Change';
        //         break;
        // }
        // return $orderStatus;
        // } else return '|' . $order->stateLabel();
    }

    public function printToPOS(Order $order)
    {
        try {
            // $this->print_POS_Category_wise_Token($order);
            PrintToPOS::dispatch($order, auth()->user()); //->delay(now()->addSeconds(10));;
            // $this->print_POS_Order($order);
        } catch (Exception $e) {
            logger('Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed xxx: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Order queued for printing');
    }
    public function printToPOSQT(Order $order)
    {
        // try {
        PrintToPOS::dispatch($order, auth()->user(), $koToken = true);
        // $this->print_POS_Category_wise_Token($order);
        // $this->print_POS_Order($order);
        // } catch (Exception $e) {
        //     logger('Failed: ' . $e->getMessage());
        //     return redirect()->back()->with('error', 'Failed xxx: ' . $e->getMessage());
        // }
        return redirect()->back()->with('success', 'Order KoT queued for printing');
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
        // $printer->text("Chand Raat Festival\n");
        // $printer->text("2024\n");

        $printer->text(str_repeat("-", 16) . "\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setTextSize(1, 1);
        // if ($order->type) {
        //     //$printer->setTextSize(2, 2);
        //     $printer->text("Order Type:");
        //     $printer->text($order->type . "\n");
        // }
        $printer->text("POS Order Receipt: ");
        $printer->text($order->POS_number . "\n");

        $printer->text("Customer: ");
        if ($order->customer) {
            $printer->text($order->customer->name . "\n");
        } else {
            $printer->text("Walk in Customer\n");
        }

        $printer->text("Order of: ");
        $printer->text($order->shop->name . "\n");

        if ($order->type) {
            $printer->text("Order Type:");
            $printer->text($order->type . "\n");
        }

        if ($order->type == 'dine-in' && $order->table_number) {
            $printer->setTextSize(2, 2);
            $printer->text("Table # ");
            $printer->text($order->table_number . "\n");
            $printer->setTextSize(1, 1);
        }
        if ($order->waiter_name) {
            $printer->text("Waiter: ");
            $printer->text($order->waiter_name  . "\n");
        }

        $printer->text("Order Date: " . $order->created_at . "\n");

        if ($order->notes) {
            $printer->text("Notes: " . $order->notes  . "\n");
        }

        //$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        //$printer->setJustification(Printer::JUSTIFY_LEFT);

        // $printer->dataHeader('POS ' . $order->POS_number);
        // $printer->setFooter("User: " . $order->user ? $order->user->getFullName() : "Guest" . "  Shop: " . $order->shop ? $order->shop->name : "Unknown");

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
            $printer->setTextSize(3, 3);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("G.Total: " . number_format((int) $order->discountedTotal(), 0) . "\n");

            $balance = $order->balance();

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);

            if ($order->state == 'closed') {
                if ($balance == 0) {
                    $printer->text("\nPAID\n");
                } else if ($balance > 0) {
                    $printer->text("\nCHIT\n");
                    $printer->text(number_format((int)$balance, 0)  . "\n");
                }
            } else {
                $printer->text("\nBalance: " . $balance  . "\n");
            }

            // $printer->setJustification(Printer::JUSTIFY_LEFT);
        }
        $printer->setTextSize(1, 1);
        $printer->text(str_repeat("-", 42) . "\n");
        $printer->text("\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
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
            $printer->text("www.quettaclub.org\n");
            $printer->text("E-mail: info@quettaclub.org");
        }
        $printer->text("\n \n");
        $printer->cut();
    }
    private function print_POS_Category_wise_Token(Order $order)
    {
        // logger('printing Cat-tokens job started');

        $items_by_category = $order->items->groupBy(function ($item) {
            return $item->product->categories[0]->kitchen_printer_ip  ?? config('settings.default_printer_ip');
        });
        // logger($items_by_category);
        foreach ($items_by_category as $ip => $items) {
            // logger('$ip');
            // logger($ip);
            // logger($items);
            $kitchen_printer_ip = $ip ?? config('settings.default_printer_ip');

            // Create order history
            $itemNamesWithQty = $items->map(function ($item) {
                return $item->product->name ?? $item->product_name . ':' . $item->quantity;
            })->implode(', ');
            $itemNamesConcatenated = $itemNamesWithQty;
            // logger($itemNamesConcatenated);
            $orderHistoryController = new OrderHistoryController();
            $orderHistoryController->store($request = null, orderId: $order->id, actionType: 'kot-printed', printerIdentifier: $kitchen_printer_ip, itemName: $itemNamesConcatenated);

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
                        $kitchen_printer->text($item->product->name ?? $item->product_name);
                        $kitchen_printer->text("\n");
                        $kitchen_printer->setJustification(Printer::JUSTIFY_RIGHT);
                        $kitchen_printer->text("Rate:(" . $item->product->price ?? $item->product_rate . ")");
                        $kitchen_printer->text("\n");

                        $kitchen_printer->setJustification(Printer::JUSTIFY_CENTER);
                        $kitchen_printer->text("\n");
                        $kitchen_printer->setTextSize(2, 2);
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

        // Create order history
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request = null, orderId: $order->id, actionType: 'pos-print-printed', printerIdentifier: $shop_printer_ip);

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
                    $shop_printer->text($item->product->name ?? $item->product_name);
                    $shop_printer->setTextSize(1, 1);
                    $shop_printer->text("\n Rate(" . $item->product->price ?? $item->product_rate . ")");
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


                if ($order->discounts->count() > 0) {

                    $shop_printer->setEmphasis(true);
                    $shop_printer->text("\n");
                    $shop_printer->setTextSize(2, 2);
                    $shop_printer->setJustification(Printer::JUSTIFY_RIGHT);
                    $shop_printer->text("Total: " . number_format((int) $order->total()) . "\n");


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

                    foreach ($order->discounts as $discount) {
                        $shop_printer->setJustification(Printer::JUSTIFY_LEFT);

                        $shop_printer->text(
                            $discount->name . '- (' . (int)$discount->percentage . " %)\n"
                        );
                    }
                    $shop_printer->setJustification(Printer::JUSTIFY_RIGHT);
                    $shop_printer->text("\n");

                    $shop_printer->setEmphasis(true);
                    $shop_printer->setTextSize(1, 1);

                    $shop_printer->text("Amount: " . number_format((int) $order->discountAmount()) . "\n");
                    $shop_printer->setEmphasis(false);

                    $shop_printer->setTextSize(1, 1);
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
    public function tokenShop()
    {
        $products =
            Category::where('name', 'tokenisable')->first()->entries(Product::class)->get();
        // = Product::where([
        //     'category' => 'tokenisable'
        // ]);
        //TODO User shop
        $userShops = auth()->user()->shops;
        $tokenShops = Shop::whereHas('categories', function ($query) {
            $query->where('type', 'product')->where('name', 'tokenisable');
        })->get();
        $validUserShops = $userShops->filter(function ($shop) {
            return $shop->categories->where('type', 'product')->where('name', 'tokenisable')->count() > 0;
        });
        $shop = $validUserShops->count() > 0 ? $validUserShops->first() : $tokenShops->first();
        //Shop::firstOrCreate(['name' => 'Token Shop']);
        $customer = Customer::firstOrCreate([
            'name' => 'Token Customer',
            'membership_number' => 999
        ]);
        $order = Order::firstOrCreate([
            'shop_id' => $shop->id,
            'user_id' => auth()->user()->id,
            'state' => 'preparing',
            'customer_id' => $customer->id
        ]);
        return view('tokenshop.main', compact('order', 'products'));
    }

    public function getFeedback(Order $order)
    {
        return view('feedback.create', compact('order'));
    }

    public function storeFeedback(Order $order, Request $request)
    {
        // dd($request);
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id',
            // 'comments' => 'required',
            'presentation_and_plating' => 'required|integer|between:1,5',
            'taste_and_quality' => 'required|integer|between:1,5',

            // Service:
            'friendliness' => 'required|integer|between:1,5',
            'service' => 'required|integer|between:1,5',
            'knowledge_and_recommendations' => 'required|integer|between:1,5',

            // Ambiance:
            'atmosphere' => 'required|integer|between:1,5',
            'cleanliness' => 'required|integer|between:1,5',

            // Value for Money:
            'overall_experience' => 'required|integer|between:1,5',

            // Comments
            'comments' => 'nullable|string',

        ]);
        // $request->request->add(['user_id' => auth()->user()->id]);
        // $request->request->add(['order_id' => $order->id]);

        Feedback::create($request->all());
        // return redirect()->back()->with('message', 'Thanks for your feedback');
        return redirect()->route('orders.index')->with('message', 'Thanks for your feedback');
    }
}
