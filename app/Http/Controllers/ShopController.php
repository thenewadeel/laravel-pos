<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shops = new Shop();
        if ($request->search) {
            $shops = $shops->where('name', 'LIKE', "%{$request->search}%");
        }
        $shops = $shops->all();
        // if (request()->wantsJson()) {
        //     return ProductResource::collection($shops);
        // }
        return $shops;
        // view('shops.index')->with('shops', $shops);
    }
}
