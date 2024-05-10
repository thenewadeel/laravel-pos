@extends('layouts.admin')


@section('title')
    {{ 'Order Edit' }}
@endsection
@section('content-header')
    {{ 'Order:Edit' }}
@endsection
@section('content-actions')
    <div class="mb-2">
        <a href="{{ route('orders.index') }}" class="btn btn-primary">{{ __('order.Index') }}</a>
        {{-- </div>
<div class="mb-2"> --}}
        <a href="{{ route('orders.show', $order) }}" class="btn btn-primary">{{ __('order.Show') }}</a>
    </div>
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    {{-- @section('route-update', route('orders.update', ['order' => $order->id])) --}}

    {{-- @section('form-fields-left') --}}
    <div class="flex row">
        <div class="card col-md-3 p-0 mx-1">
            @include('layouts.partials.orderEditData', [
                'shops' => auth()->user() ? auth()->user()->shops : $shops,
                'customers' => $customers,
                'order' => $order,
                'users' => $users,
            ])

            @include('layouts.partials.orderDiscounts', [
                'order' => $order,
                'discounts' => $discounts,
            ])

            @include('layouts.partials.orderTotals', [
                'order' => $order,
            ])

            @include('layouts.partials.orderPayments', ['order' => $order])

        </div>

        <div class="card col-md-3 p-0 mx-1">
            @include('layouts.partials.orderItemsEdit', [
                'order' => $order,
                'products' => $products,
            ])
            {{-- @endsection --}}
            {{-- @section('form-right') --}}






        </div>
        <div class="card col p-0 mx-1">
            @include('layouts.partials.orderProductsSelect', [
                'categories' => $order->shop->categories,
                // 'products' => $products,
            ])
        </div>
    @endsection
