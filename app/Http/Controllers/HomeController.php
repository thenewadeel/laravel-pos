<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Services\OrderFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (auth()->user()->type != 'admin') {
            return redirect()->route('orders.index');
        }
        // $start = microtime(true);

        $orders = Order::query();
        // $orders = $this->filterOrders($orders, $request);
        // Log::info('Starting HomeController@index', ['request' => $request->all(), 'query' => $orders->toSql(), 'bindings' => $orders->getBindings()]);
        $orders = $this->orderFilterService->applyFilters($orders, $request);
        // Log::info('Ending HomeController@index', ['modifiedQuery' => $orders->toSql(), 'modifiedQueryBindings' => $orders->getBindings()]);
        $orders = $orders
            ->whereNotNull('POS_Number')
            ->orderBy('created_at', 'desc')
            // ->with(['items', 'payments'])
            ->get();
        Log::debug('HomeController@index', ['ordersCount' => $orders->count()]);
        $orders_count = $orders->count();

        $customers_count = Customer::count();

        $sales = $orders->map(function ($i) {
            return $i->total();
        })->sum();

        $discounts = $orders->map(function ($i) {
            return $i->discountAmount();
        })->sum();

        $income = $orders->map(function ($i) {
            return $i->receivedAmount();
        })->sum();

        $balance = $orders->map(function ($i) {
            return $i->balance();
        })->sum();

        $income_today = $orders->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->map(function ($i) {
            if ($i->receivedAmount() > $i->total()) {
                return $i->total();
            }
            return $i->receivedAmount();
        })->sum();

        $orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))
            ->with('order.shop')
            ->get();

        $topSellingShops = $orderItems
            ->groupBy(function ($item) {
                return $item->order ? ($item->order->shop ? $item->order->shop->name : 'Unknown shop') : 'Unknown order';
            })
            ->map(function ($v, $k) {
                return $v->sum('price');
            })
            ->sortDesc();
        // ->take(10);

        $topValuedItems = $orderItems
            ->groupBy(function ($item) {
                return $item->product_name;
            })
            ->map(function ($v, $k) {
                return $v->sum('price');
            })
            ->sortDesc()
            ->take(25);

        $topSellingProducts = $orderItems
            ->groupBy('product_name')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortByDesc(function ($value, $key) {
                return $value;
            })
            ->take(10);

        $DateLabels = $orderItems
            ->pluck('created_at')
            ->map(function ($date) {
                $carbonDate = \Carbon\Carbon::parse($date);
                return $carbonDate->format('d (D)');
            })
            ->sort()
            ->unique()
            ->values()
            ->toArray();
        // dd($DateLabels);
        $salesData = $orders
            ->groupBy(function ($order) {
                return \Carbon\Carbon::parse($order->created_at)->format('d M D');
            })
            ->map(function ($group) {
                return $group->sum(function ($order) {
                    return $order->items->sum('price');
                });
            })
            ->values()
            ->toArray();
        // labels: @json($DateLabels),
        $mostOrdersTakenByUsers = $orders
            ->groupBy('user_id')
            ->map(function ($group) {
                return $group->count();
            })
            ->mapWithKeys(function ($value, $key) {
                return [\App\Models\User::find($key)->getFullName() => $value];
            })
            ->sortByDesc(function ($value, $key) {
                return $value;
            });
        // ->take(10);

        $mostBusyWaiters = $orders
            ->groupBy('waiter_name')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortByDesc(function ($value, $key) {
                return $value;
            })
            ->take(15);

        return view('home', [
            'orders' => $orders,
            'orders_count' => $orders_count,
            'sales' => $sales,
            'discounts' => $discounts,
            'income' => $income,
            'balance' => $balance,
            'income_today' => $income_today,
            'customers_count' => $customers_count,
            'average_order_value' => $orders->count() > 0 ? $income / $orders->count() : 0,
            'topSellingShops' => $topSellingShops,
            'topValuedItems' => $topValuedItems,
            'topSellingProducts' => $topSellingProducts,
            'DateLabels' => $DateLabels,
            'salesData' => $salesData,
            'mostOrdersTakenByUsers' => $mostOrdersTakenByUsers,
            'mostBusyWaiters' => $mostBusyWaiters,
        ]);
    }

    private function filterOrders($query, Request $request)
    {
        if (!$request->has('start_date') && !$request->has('end_date') && !$request->has('order_type') && !$request->has('order_status') && !$request->has('customer_id') && !$request->has('customer_name') && !$request->has('order_takers')) {
            return $query->whereBetween('created_at', [now()->startOfMonth(), now()]);
        }
        $orders = $query;
        Log::debug($request->all());
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'order_type' => $request->query('order_type'),
            'order_status' => $request->query('order_status'),
            'customer_id' => $request->query('customer_id'),
            'customer_name' => $request->query('customer_name'),
            'order_takers' => $request->query('order_takers'),
            'shop_ids' => $request->query('shop_ids'),
            'cashiers' => $request->query('cashiers'),
            'item_ids' => $request->query('item_ids'),
            'item_name' => $request->query('item_name'),
        ];
        $filters = array_filter($filters, function ($value) {
            return $value !== null;
        });

        // dd($filters);
        if ($request->has('start_date') && $request->query('start_date') != null) {
            if ($request->has('end_date') && $request->query('end_date') != null) {
                $orders->date_between($filters['start_date'], $filters['end_date'] ? $filters['end_date'] : now()->endOfDay());
            } else {
                $orders->start_date($filters['start_date']);
            }
        }
        //  else {
        //     $orders->whereDate('created_at', now());
        // }

        if ($request->has('order_type')) {
            $orders->where('type', $filters['order_type']);
        }
        if ($request->has('order_status')) {
            $orders->status($filters['order_status']);
        }
        if ($request->has('customer_id')) {
            $orders->customer_ids($filters['customer_ids']);
        }
        if ($request->has('customer_name')) {
            $orders->customer_name($filters['customer_name']);
        }
        if ($request->has('order_takers')) {
            $orders->order_takers($filters['order_takers']);
        }
        if ($request->has('shop_ids')) {
            $orders->shop_ids($filters['shop_ids']);
        }
        if ($request->has('cashiers')) {
            $orders->cashiers($filters['cashiers']);
        }
        if ($request->has('item_ids')) {
            $orders->item_ids($filters['item_ids']);
        }
        if ($request->has('item_name')) {
            $orders->item_name($filters['item_name']);
        }


        // $orders = $orders->filter(function (Order $order) {
        //     return $order->balance() > 0;
        // });
        return  $query;
    }
}
