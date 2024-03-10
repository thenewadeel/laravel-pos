<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $shops = new Shop();
        if ($request->search) {
            $shops = $shops->where('name', 'LIKE', "%{$request->search}%");
        }
        $shops = $shops->all();
        return  view('shop.index')->with('shops', $shops);
    }
}
