<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Reports;
use Illuminate\Http\Request;
use App\Traits\ListOf;
use App\Models\Shop;
use App\Services\OrderFilterService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class ReportsController extends Controller
{
    use ListOf;

    private $orderFilterService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct(OrderFilterService $orderFilterService)
    {
        $this->middleware('auth');
        $this->orderFilterService = $orderFilterService;
    }
    protected function getModel(): string
    {
        return Reports::class;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // dd('zzzzzzzzzzz');

        // $reports = Reports::all();
        // return view('reports.dailySale', ['reports' => $reports]);
    }
    public function dailySale(Request $request)
    {
        $shops = Shop::all();
        $orders = Order::query();
        $orders = $this->orderFilterService->applyFilters($orders, $request);

        $orders = $orders
            ->orderBy('created_at', 'desc')
            ->with(['payments.user']);
        $openOrders = clone $orders;
        $openOrders->where('state', '!=', 'closed');
        $openOrders = $openOrders->get();
        $orders = $orders
            ->where('state', 'closed')
            ->whereNotNull('POS_number')
            ->get();
        return view('reports.dailySale', compact('shops', 'orders', 'openOrders'));
    }

    public function productsReport(Request $request)
    {
        $shops = Shop::all();
        $orders = Order::query();
        $orders = $this->orderFilterService->applyFilters($orders, $request);
        $orders = $orders
            ->whereNotNull('POS_number')
            ->where('state', 'closed')
            // ->with(['payments.user'])
            ->orderBy('created_at', 'desc')
            ->pluck('id');


        $orderItems = OrderItem::query();
        $orderItems->whereIn('order_id', $orders);


        $orderItems = $orderItems
            ->orderBy('created_at', 'desc');

        $orderItems = collect($orderItems->get()
            ->groupBy('product_name')
            ->sortByDesc(function ($items) {
                return $items->sum(function ($item) {
                    return $item->price;
                });
            })
            ->map(function ($items) {
                return [
                    'product_name' => $items->first()->product_name,
                    'product_price' => $items->first()->product_rate,
                    'quantity' => $items->sum(function ($item) {
                        return $item->quantity;
                    }),
                    'amount' => $items->sum(function ($item) {
                        return $item->price;
                    }),
                    'soldQuantity' => $items->sum(function ($item) {
                        return $item->quantity;
                    }),
                    'soldAmount' => $items->sum(function ($item) {
                        return $item->price;
                    }),
                    'count' => $items->count(),
                    'totalSoldQuantity' => $items->sum(function ($item) {
                        return $item->quantity;
                    }),
                    'totalSoldAmount' => $items->sum(function ($item) {
                        return $item->price;
                    }),
                ];
            })
            ->values());



        $orderItemsData = $orderItems;
        // dd(compact('shops', 'orderItems', 'openOrdersItems'));
        return view('reports.productsReport', compact('shops', 'orderItemsData'));
    }

    public function cashiersReport(Request $request)
    {
        // dd($request->all());
        $shops = Shop::all();
        $orders = Order::query();
        $orders = $this->orderFilterService->applyFilters($orders, $request);
        $orders = $orders
            ->whereNotNull('POS_number')
            ->where('state', 'closed')
            // ->with(['payments.user'])
            ->orderBy('created_at', 'desc')
            ->pluck('id');

        $payments = Payment::query();
        $payments = $payments->whereIn('order_id', $orders);

        // $payments = $payments
        //     ->orderBy('created_at', 'desc');


        $payments = collect($payments->get()
            ->groupBy('user_id')
            ->sortByDesc(function ($payments) {
                return $payments->sum(function ($payment) {
                    return $payment->amount;
                });
            })
            ->map(function ($payments) {
                return [
                    'user' => $payments->first()->user,
                    'paymentsTotal' => $payments->sum(function ($payment) {
                        return $payment->amount;
                    }),
                    'paymentsCount' => $payments->count(),
                    'orders' => $payments->map(function ($p) {
                        return $p->order;
                    }),
                    // 'soldQuantity' => $items->sum(function ($item) {
                    //     return $item->quantity;
                    // }),
                    // 'soldAmount' => $items->sum(function ($item) {
                    //     return $item->price;
                    // }),
                    // 'count' => $items->count(),
                    // 'totalSoldQuantity' => $items->sum(function ($item) {
                    //     return $item->quantity;
                    // }),
                    // 'totalSoldAmount' => $items->sum(function ($item) {
                    //     return $item->price;
                    // }),
                ];
            })
            ->values());



        $cashiersData = $payments;
        // dd(compact('shops', 'orderItems', 'openOrdersItems'));
        return view('reports.cashiersReport', compact('shops', 'cashiersData'));
    }

    public function chitsReport(Request $request)
    {
        // dd($request->all());
        $shops = Shop::all();
        $orders = Order::query();
        $orders = $this->orderFilterService->applyFilters($orders, $request);
        $orders = $orders
            ->whereNotNull('POS_Number')
            ->where('state', 'closed')
            ->orderBy('created_at', 'desc')
            ->with(['payments.user'])
            ->get();

        $orders = $orders->filter(function (Order $order) {
            return $order->balance() > 0;
        });
        return view('reports.chitsReport', compact('shops', 'orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reports $reports)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reports $reports)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reports $reports)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reports $reports)
    {
        //
    }
    public function activities(Request $request)
    {
        $activities = Activity::orderBy('updated_at', 'desc')->limit(100)->get();
        return view('activities.index')->with('activities', $activities);
    }
}
