@extends('layouts.admin')

@section('title', __('order.Orders_List'))
@section('content-header', __('order.Orders_List'))
@section('content-actions')


    <a class="btn btn-outline-danger font-bold btn-sm" id="btn-new-order"
        onclick="document.getElementById('myModal').style.display='block'">
        <i class="fas fa-plus mr-1">
            {{ __('order.createNeworder') }}
        </i>
    </a>
    @if (auth()->user()->type == 'admin')
        <a href="{{ route('orders.index', ['all' => true]) }}" class="btn btn-info btn-sm">
            <i class="fas fa-filter mr-1"></i>{{ __('common.All') }}
        </a>
    @endif
    <a href="{{ route('orders.index', ['unpaid' => true]) }}" class="btn btn-info btn-sm">
        <i class="fas fa-filter mr-1"></i>
        {{ __('order.Unpaid') }}
    </a>
    {{-- <a href="{{ route('orders.index', ['chit' => true]) }}" class="btn btn-warning">{{ __('order.Chit_Orders') }}</a>
    <a href="{{ route('orders.index', ['discounted' => true]) }}"
        class="btn btn-secondary">{{ __('order.Discounted_Orders') }}</a> --}}
    {{-- <a href="{{ route('orders.index') }}" class="btn btn-dark btn-sm">
        <i class="nav-icon fas fa-list"></i>
        {{ __('order.title_Short') }}
    </a> --}}
    <a href="{{ route('cart.index') }}" class="btn btn-dark btn-sm">
        <i class="nav-icon fas fa-shopping-cart"></i>
        {{ __('cart.title_Short') }}
    </a>
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])
    <div id="myModal"
        class="fixed inset-0 p-4 flex flex-wrap justify-center items-center w-full h-full z-[1000] before:fixed before:inset-0 before:w-full before:h-full before:bg-[rgba(0,0,0,0.5)] overflow-auto font-[sans-serif] top-20 md:left-[40vw]"
        style="display: none">
        <div class="modal-content w-full max-w-lg bg-white shadow-lg rounded-md p-6 relative">
            <div class="close self-end border-4 m-1 border-red-500 w-full rounded-md bg-red-600 text-center"
                onclick="document.getElementById('myModal').style.display='none'" style="color: white;font-weight: bold;">
                Cancel &times;
            </div>
            @include('layouts.partials.orderCreate', [
                // 'user' => auth()->user() ? auth()->user()->shops : $shops,
            ])
        </div>
    </div>
    <div class="card">
        {{-- {{var_dump($orders)}} --}}
        <div class="card-body m-0 p-0 ">
            {{-- <div class="row"> --}}
            <div class="border-2  rounded-md flex flex-col">
                <div
                    class=" border-2 shadow-inner rounded-md shadow-blue-300 d-flex align-items-center justify-content-center text-center text-lg p-1">
                    <span>{{ __('order.Total_Orders') }}</span>:
                    {{ $orders->count() }}
                </div>
                {{-- Filter Row --}}
                {{-- 'POS_Number
                        'Customer_Name
                        'Type
                        'Table_Number
                        'Waiter_Name
                        'Shop_Name --}}
                @if (auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
                    <form action="{{ route('orders.index') }}" method="GET">
                        <div class="form-inline flex flex-row justify-items-stretch justify-content-between  m-0 p-2">
                            <div class="input-group ">
                                <input type="date" name="start_date" class="form-control "
                                    value="{{ request('start_date') }}" />
                                {{-- <div class="col-md-5"> --}}
                                <input type="date" name="end_date" class="form-control "
                                    value="{{ request('end_date') }}" />
                            </div>
                            {{-- </div> --}}
                            {{-- <div class="col-md-2"> --}}
                            {{-- <button class="btn btn-outline-primary" type="submit">{{ __('order.submit') }}</button> --}}
                            {{-- </div> --}}
                            <input type="search" name="pos_number" placeholder="{{ __('order.POS_Number') }}"
                                value="{{ request('pos_number') }}" id="posNumber" class="form-control p-0 m-0 w-auto">
                            <input type="search" name="customer_name" placeholder="{{ __('order.Customer_Name') }}"
                                value="{{ request('customer_name') }}" id="customerName"
                                class="form-control p-0 m-0 w-auto">
                            <select name="type" id="type" class="form/orders/251/edit-control p-0 m-0 w-auto">
                                <option value="">{{ __('order.Type') }}</option>
                                <option {{ request('type') == 'dine-in' ? 'selected' : '' }} value="dine-in">
                                    {{ __('order.Dine_In') }}</option>
                                <option {{ request('type') == 'take-away' ? 'selected' : '' }} value="take-away">
                                    {{ __('order.Take_Away') }}</option>
                                <option {{ request('type') == 'delivery' ? 'selected' : '' }} value="delivery">
                                    {{ __('order.Delivery') }}</option>
                            </select>
                            <input type="search" name="table_number" placeholder="{{ __('order.Table_Number') }}"
                                value="{{ request('table_number') }}" id="tableNumber" class="form-control p-0 m-0 w-auto">
                            <input type="search" name="waiter_name" placeholder="{{ __('order.Waiter_Name') }}"
                                value="{{ request('waiter_name') }}" style="width:200px" id="waiterName"
                                class="form-control p-0 m-0 w-auto">
                            <div class="btn-group  btn-block">
                                <button type="submit" class="btn btn-outline-primary btn-sm"><i
                                        class="fas fa-filter"></i>{{ __('common.Filter') }}</button>
                                <button type="reset" class="btn btn-outline-danger  btn-sm"><i
                                        class="fas fa-eraser"></i>{{ __('common.Reset') }}</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
            {{-- </div> --}}
            {{-- {{ $orders[0] }} --}}
            <table class="table table-responsive table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        {{-- <th>{{ __('order.ID') }}</th> --}}
                        <th class="col-1 align-middle">{{ __('order.POS_Number') }}</th>
                        {{-- <th>{{ __('order.Date') }}</th> --}}
                        <th class="col-2 text-center align-middle">{{ __('order.Customer_Name') }}</th>
                        <th class="col-1 text-center align-middle">{{ __('order.Type') }}</th>
                        <th class="col-1 text-center align-middle">{{ __('order.Table_Number') }}</th>
                        <th class="col-1 text-center align-middle">{{ __('order.Waiter_Name') }}</th>
                        {{-- <th>{{ __('order.Shop_Name')   }}</th> --}}
                        {{-- <th>{{ __('order.Total') }}</th> --}}
                        {{-- <th>{{ __('order.Discounts') }}</th> --}}
                        {{-- <th>{{ __('order.Discount') }}</th> --}}
                        <th class="text-center align-middle">{{ __('order.NetAmount') }}</th>
                        @if (auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
                            <th class="text-center align-middle">{{ __('order.Received_Amount') }}</th>
                            <th class="text-center align-middle">{{ __('order.Chit') }}</th>

                            {{-- <th>{{ 'Shop' }}</th> --}}
                            <th class="col-1 text-center align-middle">{{ __('order.Taken_By') }}</th>
                            <th class="col-1 text-center align-middle">{{ __('order.Closed_By') }}</th>
                        @endif
                        <th class="col-1 text-center align-middle">{{ __('order.Status') }}</th>
                        <th class="col-1 text-center align-middle">{{ __('order.Actions') }}</th>
                    </tr>


                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            {{-- <td>{{ $order->id }}</td> --}}
                            <td title="{{ $order }}" class="px-1 m-0 align-middle">
                                <a href="{{ route('orders.show', $order) }}">
                                    {{ $order->POS_number }}
                                </a>
                            </td>
                            {{-- <td>{{ $order->created_at->format('d-M-y') }}</td> --}}
                            <td class=" align-middle">{{ $order->getCustomerName() }}</td>
                            <td class="text-center align-middle">{{ $order->type }}</td>
                            <td class="text-center align-middle">{{ $order->table_number }}</td>
                            <td class=" align-middle">{{ $order->waiter_name }}</td>
                            {{-- <td>{{ $order->shop->name }}</td> --}}
                            {{-- <td>{{ config('settings.currency_symbol') }} {{ $order->formattedTotal() }}</td> --}}
                            {{-- <td>
                                @if ($order->discounts()->count() == 0)
                                    {{ 'None' }}
                                @else
                                    @foreach ($order->discounts()->get() as $discount)
                                        {{ $discount->name }} ({{ $discount->percentage }}%),
                                    @endforeach
                                @endif
                            </td> --}}
                            {{-- <td style="text-align:right;">

                                {{ config('settings.currency_symbol') }} {{ number_format($order->discountAmount(), 2) }}
                            </td> --}}
                            <td class="text-right align-middle">

                                {{ config('settings.currency_symbol') }} {{ number_format($order->discountedTotal(), 0) }}
                            </td>
                            @if (auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
                                <td class="text-right align-middle">{{ config('settings.currency_symbol') }}
                                    {{ number_format($order->receivedAmount(), 0) }}</td>

                                <td class="text-right align-middle">{{ config('settings.currency_symbol') }}
                                    {{ number_format($order->balance(), 0) }}
                                </td>
                                <td class=" align-middle">{{ $order->getUserName() }}</td>
                                @php($users = $order->payments->pluck('user')->flatten()->unique('id'))
                                <td class=" align-middle">
                                    @if ($users->isNotEmpty())
                                        @foreach ($users as $user)
                                            {{ $user->getFullName() }}@if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    @else
                                        Unknown
                                    @endif
                                </td>
                            @endif
                            <td style="vertical-align: middle; text-align: center;" class="px-0  align-middle">
                                @if ($order->state == 'preparing')
                                    {{-- <span class="badge badge-success">{{ __('order.Preparing') }}</span> --}}
                                    <span class="badge badge-primary">Open </span>
                                @elseif($order->state == 'served')
                                    {{-- <span class="badge badge-warning">{{ __('order.Served') }}</span> --}}
                                    <span class="badge badge-primary">Open </span>
                                @elseif($order->state == 'wastage')
                                    {{-- <span class="badge badge-dark">{{ __('order.Wastage') }}</span> --}}
                                    <span class="badge badge-primary">Open </span>
                                @elseif($order->state == 'closed')
                                    {{-- <span class="badge badge-danger">{{ __('order.Closed') }}</span> --}}



                                    @if ($order->stateLabel() == __('order.Not_Paid'))
                                        <span class="badge badge-danger">
                                        @elseif($order->stateLabel() == __('order.Partial'))
                                            <span class="badge badge-warning">
                                            @elseif($order->stateLabel() == __('order.Paid'))
                                                <span class="badge badge-success">
                                                @else
                                                    {{-- if($order->stateLabel() == __('order.Change')) --}}
                                                    <span class="badge badge-info">
                                    @endif
                                    {{ $order->stateLabel() }}</span>
                                @endif
                            </td>
                            <td class="p-0 m-0 text-right  align-middle">
                                <div class="btn-group">
                                    @if ($order->state != 'closed')
                                        <a href="{{ route('orders.edit', $order) }}"
                                            class="btn btn-outline-primary btn-sm py-0 my-0 px-2 align-middle">
                                            <i class="fas fa-edit text-xs "></i>
                                        </a>
                                    @endif
                                    {{-- <a href="{{ route('orders.show', $order) }}"
                                        class="btn btn-info btn-sm py-0 my-0 px-2 align-middle">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a> --}}
                                </div>
                                @include('layouts.partials.orderPrintBtns', ['order' => $order])
                            </td>
                        </tr>
                        @if ($order->notes)
                            <tr class="w-min h-min p-0 m-0">
                                <td colspan="5" class="w-min h-min p-0 m-0">
                                    <span class="text-muted mx-4 ">Notes: {{ $order->notes }}</span>

                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right align-middle">Totals : </th>
                        {{-- <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th> --}}
                        {{-- <th style="text-align:center;">{{ config('settings.currency_symbol') }}
                            {{ number_format($totalTotal) }}</th> --}}
                        {{-- <th></th> --}}
                        {{-- <th style="text-align:center;">{{ config('settings.currency_symbol') }}
                            {{ number_format($totalDiscountAmount) }}</th> --}}
                        <th class="text-right align-middle">{{ config('settings.currency_symbol') }}
                            {{ number_format($totalNetAmount) }}</th>
                        @if (auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
                            <th class="text-right align-middle">{{ config('settings.currency_symbol') }}
                                {{ number_format($totalReceivedAmount) }}</th>
                            <th class="text-right align-middle pr-1">{{ config('settings.currency_symbol') }}
                                {{ number_format($totalChitAmount) }}</th>
                        @endif
                        {{-- <th></th> --}}
                    </tr>
                </tfoot>
            </table>
            {{-- {{ $orders->render() }} --}}
        </div>
    </div>
@endsection
