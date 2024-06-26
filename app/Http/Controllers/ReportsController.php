<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Reports;
use Illuminate\Http\Request;
use App\Traits\ListOf;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ReportsController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return Reports::class;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // dd('zzzzzzzzzzz');

        // $reports = Reports::all();
        // return view('reports.dailySale', ['reports' => $reports]);
    }
    public function dailySale(Request $request)
    {
        // dd($request->all());
        $shops = Shop::all();

        $filters = [
            // 'shop_id' => $request->input('shop_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $orders = Order::query();

        if ($request->has('start_date')) {
            $orders->whereBetween('created_at', [$filters['start_date'], $filters['end_date'] ? $filters['end_date'] : now()->endOfDay()]);
        } else {
            $orders->whereDate('created_at', now());
        }
        // if ($request->has('shop_id')) {
        //     $orders->where('shop_id', $filters['shop_id']);
        // }
        if ($request->has('shops')) {
            $request->validate([
                'shops' => 'required|array',
                'shops.*' => 'required|exists:shops,id',
            ]);

            $orders->whereIn('shop_id', $request['shops']);
        }


        $orders = $orders
            ->orderBy('created_at', 'desc')
            ->with(['payments.user']);
        $openOrders = clone $orders;
        $openOrders->where('state', '!=', 'closed');
        $openOrders = $openOrders->get();
        $orders = $orders->where('state', 'closed')->get();
        return view('reports.dailySale', compact('shops', 'orders', 'openOrders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(Reports $reports)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reports $reports)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reports $reports)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reports $reports)
    {
        //
    }
    public function activities(Request $request)
    {
        $activities = Activity::orderBy('updated_at', 'desc')->limit(100)->get();
        return view('activities.index')->with('activities', $activities);
    }
}
