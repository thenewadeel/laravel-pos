<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderHistory;

class OrderHistoryController extends Controller
{
    public function index()
    {
        $orderHistories = OrderHistory::latest()->paginate(20);
        return view('partials.orderhistory.index', compact('orderHistories'));
    }

    public function show(OrderHistory $orderHistory)
    {
        return view('partials.orderhistory.show', compact('orderHistory'));
    }

    public function store(Request $request)
    {
        $orderHistory = new OrderHistory([
            'order_id' => $request->input('order_id'),
            'user_id' => auth()->user()->id,
            'description' => $request->input('description'),
        ]);
        $orderHistory->save();
        return redirect()->route('partials.orderhistory.index');
    }
}
