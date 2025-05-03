@extends('layouts.admin')


@section('title')
    {{ 'Token Shop' }}
@endsection
@section('content-header')
    <div class="flex flex-row items-center">
        <span title="{{ $order }}" class="mr-4">
            {{ 'Token Shop' }}
        </span>
        {{-- <livewire:order-p-o-s-no :order="$order" /> --}}
    </div>
@endsection
@section('content-actions')
    {{-- <div class="mb-2">
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
    </div> --}}
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    {{-- @section('route-update', route('orders.update', ['order' => $order->id])) --}}

    {{-- @section('form-fields-left') --}}
    <div class="flex md:flex-row flex-col h-[85vh]">
        <div class="card flex flex-col col-md-3 p-0 mx-1 justify-between border-4 border-red-500">
            {{-- {{$products}} --}}

            <livewire:order-items-edit :order="$order" />

            @if (auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
                <livewire:order-token-payment :order="$order" />
            @endif

        </div>
        <div class="card flex flex-wrap w-full p-0 mx-1  border-4 border-red-500">
            @foreach ($products as $product)
                <livewire:itemCardTokenised :product="$product" :order="$order" />
            @endforeach
            {{-- @include('layouts.partials.orderProductsSelect', [
                'categories' => $order->shop?->categories,
                // 'products' => $products,
            ]) --}}
        </div>
    @endsection
