<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Reports;
use Illuminate\Http\Request;
use App\Traits\ListOf;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ReportsController extends Controller
{
    use ListOf;

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
        // dd($request->all());
        $shops = Shop::all();

        $filters = [
            // 'shop_id' => $request->input('shop_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $orders = Order::query();

        if ($request->has('start_date')) {
            $orders->whereBetween('created_at', [$filters['start_date'], $filters['end_date'] ? $filters['end_date'] : now()->endOfDay()]);
        } else {
            $orders->whereDate('created_at', now());
        }
        // if ($request->has('shop_id')) {
        //     $orders->where('shop_id', $filters['shop_id']);
        // }
        if ($request->has('shops')) {
            $request->validate([
                'shops' => 'required|array',
                'shops.*' => 'required|exists:shops,id',
            ]);

            $orders->whereIn('shop_id', $request['shops']);
        }


        $orders = $orders
            ->orderBy('created_at', 'desc')
            ->with(['payments.user']);
        $openOrders = clone $orders;
        $openOrders->where('state', '!=', 'closed');
        $openOrders = $openOrders->get();
        $orders = $orders->where('state', 'closed')->get();
        return view('reports.dailySale', compact('shops', 'orders', 'openOrders'));
    }

    public function productsReport(Request $request)
    {
        // dd($request->all());
        $shops = Shop::all();

        $filters = [
            // 'shop_id' => $request->input('shop_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $orderItems = OrderItem::query();

        if ($request->has('start_date')) {
            $orderItems->whereBetween('created_at', [$filters['start_date'], $filters['end_date'] ? $filters['end_date'] : now()->endOfDay()]);
        } else {
            $orderItems->whereDate('created_at', now());
        }
        // if ($request->has('shop_id')) {
        //     $orders->where('shop_id', $filters['shop_id']);
        // }
        if ($request->has('shops')) {
            $request->validate([
                'shops' => 'required|array',
                'shops.*' => 'required|exists:shops,id',
            ]);

            $orderItems->whereHas('order', function ($query) use ($request) {
                $query->whereIn('shop_id', $request['shops']);
            });
        }

        $orderItems = $orderItems
            ->orderBy('created_at', 'desc');
        // TODO: filter the query here, result should be like {name:orderItems->product->name, quantity:orderItems->sum of quantity, amount:orderItems->sum of all prices}
        // ->sortBy(function ($items) {
        //     return $items->first()->product->price;
        // })

        $orderItems = collect($orderItems->get()
            ->groupBy('product_id')
            ->sortByDesc(function ($items) {
                return $items->sum(function ($item) {
                    return $item->price;
                });
            })
            ->map(function ($items) {
                return [
                    'product' => $items->first()->product,
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

        $filters = [
            // 'shop_id' => $request->input('shop_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $payments = Payment::query();

        if ($request->has('start_date')) {
            $payments->whereBetween('created_at', [$filters['start_date'], $filters['end_date'] ? $filters['end_date'] : now()->endOfDay()]);
        } else {
            $payments->whereDate('created_at', now());
        }
        // if ($request->has('shop_id')) {
        //     $orders->where('shop_id', $filters['shop_id']);
        // }
        if ($request->has('shops')) {
            $request->validate([
                'shops' => 'required|array',
                'shops.*' => 'required|exists:shops,id',
            ]);

            $payments->whereHas('order', function ($query) use ($request) {
                $query->whereIn('shop_id', $request['shops']);
            });
        }

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
