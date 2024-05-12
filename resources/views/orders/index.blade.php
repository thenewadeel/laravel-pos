@extends('layouts.admin')

@section('title', __('order.Orders_List'))
@section('content-header', __('order.Orders_List'))
@section('content-actions')
    @if (auth()->user()->type == 'admin')
        <a href="{{ route('orders.index', ['all' => true]) }}" class="btn btn-info">
            <i class="fas fa-filter mr-1"></i>{{ __('common.All') }}
        </a>
    @endif
    <a href="{{ route('orders.index', ['unpaid' => true]) }}" class="btn btn-info">
        <i class="fas fa-filter mr-1"></i>{{ __('order.Unpaid') }}
    </a>
    {{-- <a href="{{ route('orders.index', ['chit' => true]) }}" class="btn btn-warning">{{ __('order.Chit_Orders') }}</a>
    <a href="{{ route('orders.index', ['discounted' => true]) }}"
        class="btn btn-secondary">{{ __('order.Discounted_Orders') }}</a> --}}
    <a href="{{ route('cart.index') }}" class="btn btn-primary">{{ __('cart.title') }}</a>
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

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
                            value="{{ request('customer_name') }}" id="customerName" class="form-control p-0 m-0 w-auto">
                        <select name="type" id="type" class="form-control p-0 m-0 w-auto">
                            <option value="">{{ __('order.Type') }}</option>
                            <option {{ request('type') == 'dine-in' ? 'selected' : '' }} value="dine-in">
                                {{ __('order.Dine_In') }}</option>
                            <option {{ request('type') == 'take_away' ? 'selected' : '' }} value="take_away">
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
            </div>
            {{-- </div> --}}
            {{-- {{ $orders[0] }} --}}
            <table class="table table-responsive table-bordered table-sm">
                <thead>
                    <tr>
                        {{-- <th>{{ __('order.ID') }}</th> --}}
                        <th>{{ __('order.POS_Number') }}</th>
                        {{-- <th>{{ __('order.Date') }}</th> --}}
                        <th>{{ __('order.Customer_Name') }}</th>
                        <th>{{ __('order.Type') }}</th>
                        <th>{{ __('order.Table_Number') }}</th>
                        <th>{{ __('order.Waiter_Name') }}</th>
                        {{-- <th>{{ __('order.Shop_Name')   }}</th> --}}
                        {{-- <th>{{ __('order.Total') }}</th> --}}
                        {{-- <th>{{ __('order.Discounts') }}</th> --}}
                        {{-- <th>{{ __('order.Discount') }}</th> --}}
                        <th>{{ __('order.NetAmount') }}</th>
                        <th>{{ __('order.Received_Amount') }}</th>
                        <th>{{ __('order.Chit') }}</th>


                        {{-- <th>{{ 'Shop' }}</th> --}}
                        <th>{{ __('order.Taken_By') }}</th>
                        <th>{{ __('order.Closed_By') }}</th>
                        <th>{{ __('order.Status') }}</th>
                        <th>{{ __('order.Actions') }}</th>
                    </tr>


                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            {{-- <td>{{ $order->id }}</td> --}}
                            <td title="{{ $order }}" class="px-1 m-0">{{ $order->POS_number }}</td>
                            {{-- <td>{{ $order->created_at->format('d-M-y') }}</td> --}}
                            <td>{{ $order->getCustomerName() }}</td>
                            <td>{{ $order->type }}</td>
                            <td>{{ $order->table_number }}</td>
                            <td>{{ $order->waiter_name }}</td>
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
                            <td class="text-right">

                                {{ config('settings.currency_symbol') }} {{ number_format($order->discountedTotal(), 0) }}
                            </td>
                            <td class="text-right">{{ config('settings.currency_symbol') }}
                                {{ number_format($order->receivedAmount(), 0) }}</td>

                            <td class="text-right">{{ config('settings.currency_symbol') }}
                                {{ number_format($order->balance(), 0) }}
                            </td>

                            <td>{{ $order->getUserName() }}</td>
                            @php($users = $order->payments->pluck('user')->flatten()->unique('id'))
                            <td>
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
                            <td style="vertical-align: middle; text-align: center;" class="px-0">
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
                                    <span class="badge badge-danger">{{ __('order.Closed') }}</span>



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
                            <td class="p-0 m-0 text-right">
                                <div class="btn-group">
                                    @if ($order->state != 'closed')
                                        <a href="{{ route('orders.edit', $order) }}"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit text-xs "></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                </div>
                                @include('layouts.partials.orderPrintBtns', ['order' => $order])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right">Totals : </th>
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
                        <th class="text-right">{{ config('settings.currency_symbol') }}
                            {{ number_format($totalNetAmount) }}</th>
                        <th class="text-right">{{ config('settings.currency_symbol') }}
                            {{ number_format($totalReceivedAmount) }}</th>
                        <th class="text-right pr-1">{{ config('settings.currency_symbol') }}
                            {{ number_format($totalChitAmount) }}</th>
                        {{-- <th></th> --}}
                    </tr>
                </tfoot>
            </table>
            {{-- {{ $orders->render() }} --}}
        </div>
    </div>
@endsection
