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
use App\Models\Floor;
use App\Models\RestaurantTable;
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
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        } else if ($start_date) {
            $query->whereDate('created_at', $start_date);
        } else {
            $query->whereDate('created_at', now()->startOfDay());
        }

        Log::debug('OrderController@handleDateFilter', ['modifiedQuery' => $query->toSql(), 'modifiedQueryBindings' => $query->getBindings()]);

        return $query;
    }

    private function handleOrderFilters(Request $request, $orderQuery)
    {
        // Log::info('OrderController@handleOrderFilters', ['request' => $request->all()]);

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

        Log::debug('OrderController@handleOrderFilters', ['modifiedQuery' => $orderQuery->toSql(), 'modifiedQueryBindings' => $orderQuery->getBindings()]);

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


        if ($request->has('order_ids')) {
            $ids = explode(',', $request->input('order_ids'));
            $orders = Order::whereIn('id', $ids);
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

    /**
     * Show Vue-based order edit interface
     */
    public function vueEdit(Order $order)
    {
        if ($order->state == 'closed') {
            return back()->with('message', 'Order is already closed');
        }
        
        $order = $order->load(['items.product', 'payments', 'customer', 'shop.categories']);
        $discounts = Discount::orderBy('type')->get();
        $customers = Customer::select('id', 'name', 'membership_number')->get();
        
        // Prepare categories with products for Vue component
        $categories = Category::all()->map(function($category) {
            $products = $category->entries(Product::class)->get()->filter(function($product) {
                return $product->is_available !== false;
            })->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'is_available' => $product->is_available,
                    'low_stock_threshold' => $product->low_stock_threshold ?? 10
                ];
            });
            
            return [
                'id' => $category->id,
                'name' => $category->name,
                'products' => $products->values()->all()
            ];
        })->filter(function($category) {
            return count($category['products']) > 0;
        })->values()->all();
        
        return view('orders.vue.edit', compact('order', 'categories', 'discounts', 'customers'));
    }

    /**
     * Show Vue-based orders workspace (tabbed interface)
     */
    public function workspace(Order $order)
    {
        if ($order->state == 'closed') {
            return back()->with('message', 'Order is already closed');
        }
        
        $order = $order->load(['items.product', 'payments', 'customer', 'shop.categories']);
        $discounts = Discount::orderBy('type')->get();
        $customers = Customer::select('id', 'name', 'membership_number')->get();
        
        // Prepare categories with products for Vue component
        $categories = Category::all()->map(function($category) {
            $products = $category->entries(Product::class)->get()->filter(function($product) {
                return $product->is_available !== false;
            })->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $product->quantity,
                    'is_available' => $product->is_available,
                    'low_stock_threshold' => $product->low_stock_threshold ?? 10
                ];
            });
            
            return [
                'id' => $category->id,
                'name' => $category->name,
                'products' => $products->values()->all()
            ];
        })->filter(function($category) {
            return count($category['products']) > 0;
        })->values()->all();
        
        return view('orders.vue.workspace', compact('order', 'categories', 'discounts', 'customers'));
    }

    /**
     * Show floor and restaurant management view
     */
    public function floorRestaurant()
    {
        // Get floors with tables for the user's current shop
        $shopId = auth()->user()->current_shop_id ?? auth()->user()->shops->first()->id ?? 1;
        
        $floors = Floor::with(['tables' => function($query) {
                $query->where('is_active', true)->orderBy('table_number');
            }])
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function($floor) {
                return [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'tables' => $floor->tables->map(function($table) {
                        // Get active order for this table
                        $activeOrder = $table->getActiveOrder();
                        
                        return [
                            'id' => $table->id,
                            'number' => $table->table_number,
                            'capacity' => $table->capacity ?? 4,
                            'status' => $table->status,
                            'currentOrder' => $activeOrder ? [
                                'id' => $activeOrder->id,
                                'total' => $activeOrder->total_amount,
                                'status' => $activeOrder->state,
                                'created_at' => $activeOrder->created_at,
                                'items' => $activeOrder->items->map(function($item) {
                                    return [
                                        'id' => $item->id,
                                        'name' => $item->product_name,
                                        'quantity' => $item->quantity,
                                        'price' => $item->total_price
                                    ];
                                })
                            ] : null
                        ];
                    })
                ];
            });

        // Get today's stats
        $dailyStats = [
            'total' => Order::whereDate('created_at', today())->sum('total_amount'),
            'orders' => Order::whereDate('created_at', today())->count()
        ];

        // Get initial order for workspace link
        $initialOrder = Order::whereDate('created_at', today())
            ->where('state', '!=', 'closed')
            ->first();

        if (!$initialOrder) {
            // Create a dummy order for the link
            $initialOrder = new Order(['id' => 1]);
        }

        return view('floor.restaurant', compact('floors', 'dailyStats', 'initialOrder'));
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
        // Validate stock availability if items are provided in request
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                if (!$product) {
                    return redirect()->back()->withErrors(['items' => 'Product not found']);
                }
                if ($product->quantity < $itemData['quantity']) {
                    return redirect()->back()->withErrors(['items' => "Insufficient stock for product: {$product->name}"]);
                }
            }
        }
        
        // logger($request);
        $order = Order::create([
            'customer_id' => $request->customer_id,
            'user_id' => $request->user()->id,
            'shop_id' => $request->shop_id,
            'table_number' => $request->table ?? $request->table_number,
            'waiter_name' => $request->waiter_name,
            'type' => $request->type ?? $request->order_type ?? 'dine-in',
            'notes' => $request->notes,
        ]);

        // Create order history for creation
        $orderHistoryController = new OrderHistoryController();
        $orderHistoryController->store($request, $order->id, 'created');

        // Handle items from request or cart
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);
                $order->items()->create([
                    'price' => $itemData['price'] * $itemData['quantity'],
                    'quantity' => $itemData['quantity'],
                    'product_id' => $itemData['product_id'],
                    'unit_price' => $itemData['price'],
                    'total_price' => $itemData['price'] * $itemData['quantity'],
                    'product_name' => $product->name,
                    'product_rate' => $product->price,
                ]);
                
                // Deduct product quantity
                $product->quantity -= $itemData['quantity'];
                $product->save();
            }
        } else {
            $cart = $request->user()->cart()->get();
            foreach ($cart as $item) {
                $order->items()->create([
                    'price' => $item->price * $item->pivot->quantity,
                    'quantity' => $item->pivot->quantity,
                    'product_id' => $item->id,
                    'unit_price' => $item->price,
                    'total_price' => $item->price * $item->pivot->quantity,
                    'product_name' => $item->name,
                    'product_rate' => $item->price,
                ]);
                
                // Deduct product quantity
                $item->quantity -= $item->pivot->quantity;
                $item->save();
            }
            $request->user()->cart()->detach();
        }
        
        if ($request->amount) {
            $order->payments()->create([
                'amount' => $request->amount,
                'user_id' => $request->user()->id,
            ]);
        }
        if ($request->discountsToAdd) {
            $order->discounts()->sync($request->discountsToAdd);
        }
        
        return redirect()->route('orders.show', $order)->with('success', 'Order created successfully');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'state' => 'required|in:preparing,served,closed,wastage'
        ]);

        $order->state = $request->state;
        $order->save();

        return redirect()->route('orders.show', $order)->with('success', 'Order status updated successfully');
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

    /**
     * Download a PDF of the order with the given ID
     *
     * @param int $id The ID of the order to download
     * @return \Illuminate\Http\Response The PDF file
     */
    private function getOrderStatus(Order $order)
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
        // try {
        // $this->print_POS_Category_wise_Token($order);
        PrintToPOS::dispatch($order, auth()->user()); //->delay(now()->addSeconds(10));;
        // $this->print_POS_Order($order);
        // } catch (Exception $e) {
        // logger('Failed: ' . $e->getMessage());
        //     return redirect()->back()->with('error', 'Failed xxx: ' . $e->getMessage());
        // }

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
