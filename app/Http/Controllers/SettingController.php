<?php

namespace App\Http\Controllers;

use App\Jobs\OffloadOrders;
use App\Jobs\OffloadOrdersJob;
use App\Models\JobLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Traits\ListOf;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return Setting::class;
    }
    public function index()
    {
        return view('settings.edit');
    }

    public function store(Request $request)
    {
        $data = $request->except('_token');
        foreach ($data as $key => $value) {
            $setting = Setting::firstOrCreate(['key' => $key]);
            $setting->value = $value;
            $setting->save();
        }

        return redirect()->route('settings.index');
    }
    public function ordersExporterBurner(Request $request)
    {

        // $job = new OffloadOrders(Order::where('state', "<>", 'closed')->inRandomOrder()->limit(100)->get(), auth()->user());
        // $pendingDispatch = $job->dispatch(Order::where('state', "<>", 'closed')->limit(100)->get(), auth()->user()); // Dispatch the job

        // $jobId = $pendingDispatch->getJobId(); // Get the job ID *after* dispatching

        // // Store the job details and user ID in the database
        // $jobLog = new JobLog();
        // $jobLog->user_id = auth()->user()->id;
        // $jobLog->job_name = 'OffloadOrders';
        // $jobLog->job_id = $jobId; // Store the job ID
        // $jobLog->status = 'pending';
        // $jobLog->save();

        // Log::info("Dispatched job with ID: " . $jobId);



        OffloadOrders::dispatch(Order::NotBurnt()->get(), auth()->user());
        return back()->with('success', "Dispatched job with ID: ");
    }
    // public function ordersExporterMonitor(Request $request)
    // {
    //     OffloadOrders::dispatch(Order::where('state', "<>", 'closed')->limit(100)->get(), auth()->user(), false);
    //     return back()->with('success', 'Monitor running');
    // }
    public function clearProducts(Request $request)
    {
        Product::query()->delete();
        return back()->with('success', 'All products deleted');
    }
}
