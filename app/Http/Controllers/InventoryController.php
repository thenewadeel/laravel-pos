<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use Maatwebsite\Excel\Facades\Excel;

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

    public function import(Request $request)
    {
        // logger($request->all());
        // logger('star    ');
        // $validationResult = $request->validate([
        //     'file' => 'required|file|mimes:xlsx'
        // ]);
        $request->validate([
            'file' => 'required|file|mimes:xlsx'
        ]);

        // logger('$file');
        $file = $request->file('file')->store('storage');
        // logger($file);
        // $import = new \App\Imports\InventoryImport;
        // $import->import($file);
        Excel::import(new \App\Imports\InventoryImport, $file);
        // Excel::import(new InventoryImport, 'users.xlsx');

        // return redirect('/')->with('success', 'All good!');
        return redirect()->route('inventory.index')->with('success', 'Inventory data imported successfully.');
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
