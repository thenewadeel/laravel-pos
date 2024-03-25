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
            $orders = Order::where('user_id', auth()->user()->id);
        }
        if ($request->has('state')) {
            $filters = array_intersect(['preparing', 'served', 'closed', 'wastage'], $request->state);
            $orders = $orders->whereIn('state', $filters);
        }

        if ($request->has('type')) {
            $filters = array_intersect(['dine-in', 'take-away', 'delivery'], $request->type);
            $orders = $orders->whereIn('type', $filters);
        }
        // $today = now()->startOfDay();
        // if ($request->start_date && $request->end_date) {
        //     $orders = $orders->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        // } else {
        //     $orders = $orders->whereDate('created_at', $today);
        // }

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

        $shops = Shop::all();
        $customers = Customer::all();
        return view('orders.edit', compact('order', 'shops', 'customers', 'users'));
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

        return redirect()->route('orders.edit', $order)->with('success', 'Payment added successfully');
    }


    public function destroyPayment(Order $order, Payment $payment)
    {
        $payment->delete();

        return redirect()->route('orders.edit', $order)->with('success', 'Payment deleted successfully');
    }

    public function show(Order $order)
    {

        // get previous user id
        $previous = Order::where('id', '<', $order->id)->max('id');

        // get next user id
        $next = Order::where('id', '>', $order->id)->min('id');
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
        logger($request);
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
        return 'success';
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

    public function printPdf($id)
    {
        $order = Order::with(['items.product', 'payments', 'customer', 'shop'])
            ->findOrFail($id);
        $pdf = Pdf::loadView('pdf.order', compact('order'));
        // $pdf->setPaper([0, 0, 226.7, 700.7], 'portrait'); // A4, 70% scale
        return $pdf->download('order_' . $order->id . '.pdf');
    }
}
