<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;

class InventoryController extends Controller
{
    // public function index()
    public function index(Request $request)
    {
        $search = $request->input('search');
        $inventoryItems = InventoryItem::when($search, function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        })->orderBy('id', 'desc')->get();
        return view('inventory.index', compact('inventoryItems'));
    }

    public function issue(Request $request)
    {
        return view('inventory.issue');
    }

    public function deposit(Request $request)
    {
        return view('inventory.deposit');
    }

    public function stockTaking(Request $request)
    {
        return view('inventory.stock_taking');
    }

    public function evaluation(Request $request)
    {
        return view('inventory.evaluation');
    }
}
