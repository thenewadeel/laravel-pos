<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Exports\QueryExport;
use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ShopResource;
use App\Http\Requests\ShopUpdateRequest;
use App\Http\Requests\ShopStoreRequest;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ShopOrdersExport;
use App\Exports\UsersExport;
// use App\Models\Category;
use AliBayat\LaravelCategorizable\Category;
use App\Exports\ShopsExport;
use App\Imports\ShopsImport;
use App\Models\Order;
use App\Traits\ListOf;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return Shop::class;
    }
    public function listOf1(Request $request)
    {
        $records = Shop::query();

        if ($request->search) {
            $records->where('name', 'LIKE', "%{$request->search}%");
        }

        return $records->get();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $shops = new Shop();
        if ($request->search) {
            $shops = $shops->where('name', 'LIKE', "%{$request->search}%");
        }
        $itemsPerPage = 100;
        if ($request->itemCount) {
            $itemsPerPage = $request->itemCount;
        }
        $shops = $shops->with('categories');
        $shops = $shops->with(['users' => function ($query) use ($request) {
            if ($request->search) {
                $query->where('name', 'LIKE', "%{$request->search}%");
            }
        }])->paginate($itemsPerPage);

        return  view('shops.index')->with('shops', $shops);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('shops.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShopStoreRequest $request)
    {
        // dd([
        //     'name' => $request->name,
        //     'description' => $request->description,
        //     // 'image' => $image_path,
        //     'user_id' => $request->user_id
        // ]);
        $image_path = '';

        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('shops', 'public');
        }

        $shop = Shop::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $image_path,
            'user_id' => $request->user_id,
        ]);

        if (!$shop) {
            return redirect()->back()->with('error', __('shop.error_creating'));
        }
        return redirect()->route('shops.index')->with('success', __('shop.success_creating'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function show(Shop $shop, Request $request)
    {
        // logger('request');
        // logger($request);
        // $filters = $request->only(['shop_id']);
        // $orders = $this->getOrders($shop, $filters)->get();
        $start = now()->startOfMonth();
        $end = now();
        $orders = Order::where('shop_id', $shop->id)
            ->where('state', 'closed')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get();
        $categories = Category::all();
        // get previous user id
        $previous = Shop::where('id', '<', $shop->id)
            // ->where('user_id', $shop->user_id)
            ->max('id');


        // get next user id
        $next = Shop::where('id', '>', $shop->id)
            // ->where('user_id', $shop->user_id)
            ->min('id');

        return view('shops.show', compact('shop',  'categories', 'orders', 'next', 'previous'));
    }

    private function getOrders(Shop $shop, array $filters)
    {
        $ordersQ = \App\Models\Order::query();
        $this->applyFilters($ordersQ, $filters);
        $ordersQ->where('shop_id', $shop->id);

        return $ordersQ;
    }

    private function applyFilters($ordersQ, array $filters)
    {
        if (isset($filters['created_at'])) {
            $ordersQ->whereDate('created_at', $filters['created_at']);
        } else {
            //orders of current month
            $ordersQ->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ]);
        }
    }


    public function exportReport(Shop $shop, Request $request)
    {
        $filters = $request->only(['created_at', 'shop_id']);
        $orders = $this->getOrders($shop, $filters);

        return Excel::download(new ShopOrdersExport($orders->get()), 'shop-export.xlsx');
    }

    /**
     * Controller that handles the export
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    // public function exportReport(Request $request, Shop $shop)
    // {
    //     $from = Carbon::parse($request->input('from'));
    //     $to = Carbon::parse($request->input('to'));

    //     $query = \App\Models\Order::query();
    //     $query->where('shop_id', $shop->id);
    //     $query->whereBetween('created_at', [$from, $to]);

    //     return Excel::download(new QueryExport($query), 'shop-export.xlsx');
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */

    public function edit(Shop $shop, Request $request)
    {
        $categories = Category::where('type', 'product')->get();

        return view('shops.edit', compact('shop',  'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function update(ShopUpdateRequest $request, Shop $shop)
    {
        $image_path = '';

        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('shops', 'public');
        }

        $shop->name = $request->name;
        $shop->description = $request->description;
        $shop->printer_ip = $request->printer_ip;
        $shop->image = $image_path;

        // Update shop via pivot table user_shops
        $shop->users()->syncWithoutDetaching([$request->user_id]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($shop->image) {
                Storage::delete($shop->image);
            }
            // Store image
            $image_path = $request->file('image')->store('shops', 'public');
            // Save to Database
            $shop->image = $image_path;
        }
        error_log("jfgjgfcj");
        if (!$shop->save()) {
            return redirect()->back()->with('error', __('shop.error_updating'));
        }
        return redirect()->route('shops.index')->with('success', __('shop.success_updating'));
    }

    /**
     * Assign multiple shops to this user via pivot table user_shop.
     */
    public function updateCategories(Shop $shop, Request $request)
    {
        // dd($request);
        if ($request->has('category')) {
            $request->validate([
                'category' => 'required|array',
                'category.*' => 'required|exists:categories,id',
            ]);

            $shop->categories()->sync($request->category);
        } else {
            $shop->categories()->detach();
        }

        return redirect()->back()->with('success', __('shop.success_updating_categories'));



        // dd($request->all());
        // $request->validate([
        //     'shop_ids' => 'required|array',
        //     'shop_ids.*' => 'required|exists:shops,id',
        // ]);
        // $user->shops()->sync($request->shop_ids);

        // return redirect()->route('users.edit', $user)->with('success', 'Shop assigned successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop)
    {
        if ($shop->image) {
            Storage::delete($shop->image);
        }
        $shop->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true
            ]);
        }
        return back()->with('message', "Shop deleted");
    }


    public function __destruct()
    {
        // This method is called when the object is destroyed.
        // It can be used to clean up resources like files, database connections, etc.
        // In this case, there's nothing to clean up, so we don't need to do anything.
    }

    public function export()
    {
        return Excel::download(new ShopsExport, 'shops.xlsx');
    }
    public function import()
    {
        if (request()->file('xlsx_file')) {
            // Excel::import(new UsersImport, 'users.xlsx');

            Excel::import(new ShopsImport, request()->file('xlsx_file'));

            return redirect('/shops')->with('success', 'All good!');
        } else {
            return redirect()->back()->with('failure', 'File err');
        }
    }

    /**
     * Show products management page for a shop
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function products(Shop $shop)
    {
        $products = \App\Models\Product::all();
        $assignedProducts = $shop->products->pluck('id')->toArray();
        
        return view('shops.products', compact('shop', 'products', 'assignedProducts'));
    }

    /**
     * Update products assigned to a shop
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function updateProducts(Request $request, Shop $shop)
    {
        $request->validate([
            'products' => 'array',
            'products.*' => 'exists:products,id',
        ]);

        $shop->products()->sync($request->products ?? []);

        return redirect()->route('shops.products', $shop)->with('success', __('shop.success_updating_products'));
    }
}
