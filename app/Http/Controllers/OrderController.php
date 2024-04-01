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
use Illuminate\Log\Logger;
use App\Jobs\PrintOrderTokensJob;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Exception;

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


        // logger('asdasdasd');
        if (auth()->user()->type == 'admin') {
            $orders = Order::query();
        } else {
            $u = User::with('shops')->find(auth()->id());
            // $u = User::with('shops')->find(auth()->id());


            $shops = $u->shops()->pluck('shops.id')->toArray();
            // dd($shops);
            $orders = Order::whereIn('shop_id', $shops);
        }
        // if ($request->has('state')) {
        //     $filters = array_intersect(['preparing', 'served', 'closed', 'wastage'], $request->state);
        //     $orders = $orders->whereIn('state', $filters);
        // }

        // if ($request->has('type')) {
        //     $filters = array_intersect(['dine-in', 'take-away', 'delivery'], $request->type);
        //     $orders = $orders->whereIn('type', $filters);
        // }
        $today = now()->startOfDay();
        if ($request->start_date && $request->end_date) {
            $orders = $orders->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        } else {
            $orders = $orders->whereDate('created_at', $today);
        }

        $unpaid = $request->has('unpaid') && $request->unpaid == '1';
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

        $orders = $orders->with(['items', 'payments', 'customer', 'shop'])->orderBy('created_at', 'desc')->paginate(25);

        $total = $orders->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();

        return view('orders.index', compact('orders', 'total', 'receivedAmount'));
    }

    public function edit(Order $order)
    {
        $order = $order->load(['items.product', 'payments', 'customer', 'shop']);
        $users = User::all();
        $discounts = Discount::all();
        $shops = Shop::all();
        $customers = Customer::all();
        $products = Product::all();
        return view('orders.edit', compact('order', 'shops', 'customers', 'users', 'discounts', 'products'));
    }


    public function update(Request $request, Order $order)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'shop_id' => 'required|exists:shops,id',
            'type' => 'required|in:dine-in,take-away,delivery',
            'state' => 'required|in:preparing,served,closed,wastage',
        ]);

        $order->update($validatedData);

        return redirect()->route('orders.index')->with('success', 'Order updated successfully');
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
        // dd($request);
        $product = Product::find($request->item);

        $validatedData = $request->validate([
            'item' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $validatedData['order_id'] = $order->id;
        $validatedData['user_id'] = $request->user()->id;
        $validatedData['product_id'] = $product->id;
        $validatedData['price'] = $product->price * $request->quantity;

        // dd($product);
        $order->items()->create($validatedData);

        // if ($product) {
        //     $order->items()->create([
        //         'price' => $product->price,
        //         'quantity' => 1,
        //         'product_id' => $request->item,
        //     ]);
        // } else {
        //     dd($request);
        // }

        return redirect()->route('orders.edit', $order)->with('success', 'Product added to order successfully');
    }

    public function destroyItem(Order $order, OrderItem $item)
    {
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
        $validatedData = $request->validate([
            'discountsToAdd' => 'nullable|array',
            'discountsToAdd.*' => 'nullable|exists:discounts,id',
        ]);
        // dd($validatedData);
        $discountsToAdd = $validatedData['discountsToAdd'] ?? [];
        $order->discounts()->sync($discountsToAdd);

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
            'type' => $request->order_type
        ]);

        $cart = $request->user()->cart()->get();
        foreach ($cart as $item) {
            $order->items()->create([
                'price' => $item->price * $item->pivot->quantity,
                'quantity' => $item->pivot->quantity,
                'product_id' => $item->id,
            ]);
            $item->quantity = $item->quantity - $item->pivot->quantity;
            $item->save();
        }
        $request->user()->cart()->detach();
        if ($request->amount) {
            $order->payments()->create([
                'amount' => $request->amount,
                'user_id' => $request->user()->id,
            ]);
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
        $pdf->setPaper([0, 0, 204, 260], 'portrait'); // 80mm thermal paper
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

    public function printToPOS($order, $ip = "192.168.0.162"): void
    {
        // logger('printing tokens job started');
        try {
            $connector = new NetworkPrintConnector($ip, 8899, $timeout = 25);
            $printer = new Printer($connector);
            try {
                // ... Print stuff
                $printer->text("Assalam o alaikum!\n");
                $printer->cut();
            } catch (Exception $e) {
                // logger($e->getMessage());
            } finally {
                $printer->close();
            }
        } catch (Exception $e) {
            // logger('Failed to connect to printer: ' . $e->getMessage());
            return;
        }
    }
}
