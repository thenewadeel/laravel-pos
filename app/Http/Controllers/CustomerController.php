<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\ListOf;

class CustomerController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return Customer::class;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->wantsJson()) {
            return response(
                Customer::all()
            );
        }
        $customers = Customer::latest()->paginate(25);
        return view('customers.index')->with('customers', $customers);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerStoreRequest $request)
    {
        $photo_path = '';

        if ($request->hasFile('photo')) {
            $photo_path = $request->file('photo')->store('customers', 'public');
        }

        $customer = Customer::create([
            'name' => $request->name,
            // 'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'photo' => $photo_path,
            'membership_number' => $request->membership_number,
            // 'user_id' => $request->user()->id,
        ]);

        if (!$customer) {
            return redirect()->back()->with('error', __('customer.error_creating'));
        }
        return redirect()->route('customers.index')->with('success', __('customer.succes_creating'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $customer->first_name = $request->first_name;
        $customer->last_name = $request->last_name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        $customer->membership_number = $request->membership_number;

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($customer->photo) {
                Storage::delete($customer->photo);
            }
            // Store photo
            $photo_path = $request->file('photo')->store('customers', 'public');
            // Save to Database
            $customer->photo = $photo_path;
        }

        if (!$customer->save()) {
            return redirect()->back()->with('error', __('customer.error_updating'));
        }
        return redirect()->route('customers.index')->with('success', __('customer.success_updating'));
    }

    public function destroy(Customer $customer)
    {
        if ($customer->photo) {
            Storage::delete($customer->photo);
        }

        $customer->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
