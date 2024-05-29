<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discount;

use App\Traits\ListOf;

class DiscountController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return Discount::class;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Discount::create([
            'name' => 'New Discount',
            'percentage' => 0,
            'amount' => 0,
            'method' => 'NATURAL',
            'type' => 'DISCOUNT'
        ]);

        return back()->with('message', "Discount created");
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //'name' => 'New Discount',
        // 'percentage' => 0,
        // 'amount' => 0,
        // 'method' => 'NATURAL',
        // 'type' => 'DISCOUNT'

        $validatedData = $request->validate([
            'name' => 'required|string',
            'percentage' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'method' => 'nullable|in:NATURAL,REVERSE|string',
            'type' => 'required|in:DISCOUNT,CHARGES|string',
            // 'user_id' => 'required|exists:users,id',
            // 'state' => 'nullable|in:preparing,served,closed,wastage',
        ]);

        // $validatedData['user_id'] = auth()->user()->id;

        // $order->update($validatedData);
        Discount::find($id)->update($validatedData);
        return back()->with('success', 'Discount updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        // dd('asdfghjk');
        $discount->delete();
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true
            ]);
        }
        return back()->with('message', "Discount deleted");
    }
}
