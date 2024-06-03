@extends('layouts.admin')


@section('title')
    {{ 'Order Edit' }}
@endsection
@section('content-header')
    {{ 'Order:Edit' }}
    <span class="text-base" title="{{ $order }}">{{ $order->POS_number }}</span>
@endsection
@section('content-actions')
    <div class="mb-2">
        {{-- <a href="{{ route('orders.index') }}" class="btn btn-primary">{{ __('order.Index') }}</a> --}}
        {{-- </div>
<div class="mb-2"> --}}
        <a href="{{ route('orders.show', $order) }}" class="btn btn-info btn-sm">
            <i class="nav-icon fas fa-eye"></i>
            {{ __('order.Show') }}
        </a>
        <a href="{{ route('orders.index') }}" class="btn btn-dark btn-sm">
            <i class="nav-icon fas fa-list"></i>
            {{ __('order.title_Short') }}
        </a>
        <a href="{{ route('cart.index') }}" class="btn btn-dark btn-sm">
            <i class="nav-icon fas fa-shopping-cart"></i>
            {{ __('cart.title_Short') }}
        </a>
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
            <livewire:order-items-edit :order="$order" />
            {{-- @include('layouts.partials.orderItemsEdit', [
                'order' => $order,
                'products' => $products,
            ]) --}}
            {{-- @endsection --}}
            {{-- @section('form-right') --}}






        </div>
        {{-- <div class="card col p-0 mx-1">
        </div> --}}
        <div class="card col p-0 mx-1">
            <div class="max-h-96">
                <livewire:item-search :order="$order" />
            </div>
            <div>

                @include('layouts.partials.orderProductsSelect', [
                    'categories' => $order->shop?->categories,
                    // 'products' => $products,
                ])
            </div>
        </div>
    @endsection
