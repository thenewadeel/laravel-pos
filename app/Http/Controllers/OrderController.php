<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Traits\ListOf;

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

    public function store(OrderStoreRequest $request)
    {
        $order = Order::create([
            'customer_id' => $request->customer_id,
            'user_id' => $request->user()->id,
            'shop_id' => $request->shop_id,
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
        $order->payments()->create([
            'amount' => $request->amount,
            'user_id' => $request->user()->id,
        ]);
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
}
