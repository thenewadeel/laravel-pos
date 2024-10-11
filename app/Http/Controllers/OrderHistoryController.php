<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderHistory;
use App\Models\OrderItem;

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

    public function store(Request $request = null, $orderId, $actionType,  $itemName = null, $itemQty = null, $printerIdentifier = null, string $paymentAmount = null, $POSNumber = null)
    {
        // dd($request, $orderId, $actionType, $printerIdentifier, $itemName, $itemQty);

        // Create order history record
        $history = new OrderHistory();
        $history->order_id = $orderId;
        $history->user_id = auth()->user()->id; // Assuming authenticated user
        $history->action_type = $actionType;
        $history->save();
        $history->description = $history->generateDescription(itemName: $itemName, itemQty: $itemQty, printerIdentifier: $printerIdentifier, paymentAmount: $paymentAmount, POSNumber: $POSNumber);
        $history->save();

        return response()->json(['message' => 'Order history created successfully']);
    }
}
