@extends('layouts.admin')


@section('title')
    {{ 'Order Edit' }}
@endsection
@section('content-header')
    <div class="flex flex-row justify-between md:justify-start space-x-2 border-0 border-cyan-300">
        <span title="{{ $order }}" class="mr-4">
            {{ 'Order:Edit' }}
        </span>
        <livewire:order-p-o-s-no :order="$order" />
    </div>
@endsection
@section('content-actions')
    <div class="flex flex-row justify-end space-x-2 border-0 border-green-500">
        @include('layouts.partials.orderPrintBtns', ['order' => $order])

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
        {{-- <a href="{{ route('cart.index') }}" class="btn btn-dark btn-sm">
            <i class="nav-icon fas fa-shopping-cart"></i>
            {{ __('cart.title_Short') }}
        </a> --}}
    </div>
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    {{-- @section('route-update', route('orders.update', ['order' => $order->id])) --}}

    {{-- @section('form-fields-left') --}}
    <div class="flex md:flex-row flex-col ">
        <div class="card flex flex-col md:w-[45%] p-0 mx-1 justify-between border-4 border-red-500">

            @if (auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
                @include('layouts.partials.orderEditData', [])
            @endif

            <livewire:order-items-edit :order="$order" />

            @if (auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
                <livewire:order-payment :order="$order" />
            @endif
        </div>
        <div class="card flex flex-col w-full p-0 mx-1  border-4 border-red-500">

            @include('layouts.partials.orderProductsSelect', [
                'categories' => $order->shop?->categories,
                // 'products' => $products,
            ])
        </div>
    </div>
@endsection
