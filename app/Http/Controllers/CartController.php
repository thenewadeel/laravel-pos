<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartStoreRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['store', 'changeQty', 'delete', 'empty']]);
    }
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response(
                $request->user()->cart()->get()
            );
        }
        return view('cart.index');
    }
    // public function index2(Request $request)
    // {
    //     if ($request->wantsJson()) {
    //         return response(
    //             $request->user()->cart()->get()
    //         );
    //     }
    //     $cart = $request->user()->cart()->get();
    //     return view('cart.index2', compact('cart'));
    // }
    public function indexTokens(Request $request)
    {
        if ($request->wantsJson()) {
            return response(
                $request->user()->cart()->get()
            );
        }
        return view('cart.indexTokens');
    }

    public function store(CartStoreRequest $request)
    {
        $id = $request->id;
        $qty = $request->has('quantity') ? $request->quantity : 1;

        $product = Product::where('id', $id)->first();
        $cart = $request->user()->cart()->where('id', $id)->first();
        if ($cart) {
            // check product quantity
            // if ($product->quantity <= $cart->pivot->quantity) {
            //     return response([
            //         'message' => __('cart.available', ['quantity' => $product->quantity]),
            //     ], 400);
            // }
            // update only quantity
            $cart->pivot->quantity = $cart->pivot->quantity + $qty;
            $cart->pivot->save();
        } else {
            // if ($product->quantity < 1) {
            //     return response([
            //         'message' => __('cart.outstock'),
            //     ], 400);
            // }
            $request->user()->cart()->attach($product->id, ['quantity' => $qty]);
        }

        return response('', 204);
    }

    public function changeQty(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        $cart = $request->user()->cart()->where('id', $request->product_id)->first();

        if ($cart) {
            // check product quantity
            // if ($product->quantity < $request->quantity) {
            //     return response([
            //         'message' => __('cart.available', ['quantity' => $product->quantity]),
            //     ], 400);
            // }
            $cart->pivot->quantity = $request->quantity;
            $cart->pivot->save();
        }

        return response([
            'success' => true
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id'
        ]);
        $request->user()->cart()->detach($request->product_id);

        return response('', 204);
    }

    public function empty(Request $request)
    {
        $request->user()->cart()->detach();

        return response('', 204);
    }
}
