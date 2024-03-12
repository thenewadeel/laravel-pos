<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ShopResource;
use App\Http\Requests\ShopUpdateRequest;
use App\Http\Requests\ShopStoreRequest;
use Illuminate\Support\Facades\Storage;
use App\Traits\ListOf;

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
        $itemsPerPage = 10;
        if ($request->itemCount) {
            $itemsPerPage = $request->itemCount;
        }
        $shops = $shops->with(['user'])->latest()->paginate($itemsPerPage);
        if (request()->wantsJson()) {
            return ShopResource::collection($shops);
        }
        return  view('shop.index')->with('shops', $shops);
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
    public function show(Shop $shop)
    {
        //TODO 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function edit(Shop $shop)
    {
        return view('shops.edit')->with('shop', $shop);
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

        $shop->image = $image_path;

        $shop->user_id = $request->user_id;

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

        return response()->json([
            'success' => true
        ]);
    }


    public function __destruct()
    {
        // This method is called when the object is destroyed.
        // It can be used to clean up resources like files, database connections, etc.
        // In this case, there's nothing to clean up, so we don't need to do anything.
    }
}
