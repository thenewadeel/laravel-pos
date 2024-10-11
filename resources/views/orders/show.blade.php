@extends('layouts.model.show')

@section('title')
    {{ 'Order Show' }}
@endsection
@section('content-header')
    {{ 'Order:Show' }}
@endsection
@section('content-actions')
    {{-- {{ $order }} --}}
    <div class="mb-2">

        {{-- <a href="{{ route('orders.index') }}" class="btn btn-primary">{{ __('order.Index') }}</a> --}}
        @if ($order->state !== 'closed')
            <a href="{{ route('orders.edit', $order) }}" class="btn btn-info btn-sm">
                <i class="nav-icon fas fa-edit"></i>
                {{ __('order.Edit') }}
            </a>
        @endif
        {{-- <a href="{{ route('orders.print', $order) }}" class="btn btn-primary ">
            {{ __('order.Print') }} <i class="fas fa-print"></i></a> --}}
        {{-- <a href="{{ route('orders.print.preview', $order) }}" class="btn btn-primary ">
            {{ __('order.Print_Preview') }} <i class="fas fa-print"></i></a> --}}
        @include('layouts.partials.orderPrintBtns', ['order' => $order])
        @if ($order->hasFeedback() || $order->type != 'dine-in')
            {{-- @include('layouts.partials.rating.preview', [
                'rating' => $order->feedback,
            ]) --}}
        @else
            <a href="{{ route('orders.getFeedback', $order) }}" class="btn btn-primary p-0 px-2 align-middle">
                <i class="fas fa-thumbs-up text-xs mr-2"></i>Feedback
            </a>
        @endif
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


@section('variables')
    @php($modelName = 'Order')
    @php($modelObject = $order)
@endsection

@section('content-details')
    <div class="card p-2 col-md-4">
        <h4>Order # {{ $order->POS_number }}</h4>
        <div class="d-flex justify-content-between">
            <span>Date Taken:</span>
            <span>{{ $order->created_at->format('d-m-Y') }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Shop:</span>
            <span>{{ $order?->shop?->name }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Customer:</span>
            <span>{{ $order?->customer?->name }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Table #:</span>
            <span>{{ $order->table_number }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Waiter:</span>
            <span>{{ $order->waiter_name }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Type:</span>
            <span>{{ $order->type }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>User:</span>
            <span>{{ $order->user->getFullname() }}</span>
        </div>
        <table class="table table-bordered">

            <tr style="text-align:center">
                <th>Item</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Amount</th>
            </tr>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td style="text-align:right">{{ number_format($item->product->price) }}</td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td style="text-align:right">{{ number_format($item->price, 2) }}</td>
                </tr>
            @endforeach

            <tr>
                <th colspan="2">Total</th>
                <td style="text-align:right" colspan="2">
                    {{ config('settings.currency_symbol') }}{{ number_format($order->total(), 2) }}</td>
            </tr>

            <tr>
                <th colspan="2">
                    <span style="font-size:16px">Discounts :</span><br>
                    <span style="font-size:12px">
                        @if ($order->discounts()->count() == 0)
                            {{ 'None' }}
                        @else
                            @foreach ($order->discounts()->get() as $discount)
                                {{ $discount->name }} ({{ number_format($discount->percentage) }}%)@if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        @endif
                    </span>
                </th>
                <td style="text-align:right" colspan="2">
                    {{ config('settings.currency_symbol') }}{{ number_format($order->discountAmount(), 2) }}</td>
            </tr>
            <tr>
                <th colspan="2">Net Amount Payable</th>
                <td style="text-align:right" colspan="2">
                    {{ config('settings.currency_symbol') }}{{ number_format($order->discountedTotal(), 2) }}</td>
            </tr>

            <tr>
                <th colspan="2">Received Amount</th>
                <td style="text-align:right" colspan="2">
                    {{ config('settings.currency_symbol') }}{{ $order->formattedReceivedAmount() }}</td>
            </tr>
            @if ($order->notes)
                <tr class="w-min h-min p-0 m-0">
                    <td colspan="5" class="w-min h-min p-0 m-0">
                        <span class="text-muted mx-4 ">Notes: {{ $order->notes }}</span>

                    </td>
                </tr>
            @endif
        </table>

        {{-- {{ $order }} --}}
    </div>
    <div class="flex flex-col bg-gray-50 rounded-md shadow-sm p-2 mx-2 ">
        <h4 class="text-lg font-bold mb-2 self-center text-center w-full bg-slate-100 rounded-md shadow-inner">Order History</h4>
        <div class="h-min overflow-y-scroll max-h-[600px]">

            @foreach ($histories as $history)
            @include('layouts.partials.orderhistory.show', ['orderHistory' => $history])
            @endforeach
        </div>
    </div>


@endsection

@section('footer-actions')
    <div class="d-flex justify-content-between w-100 md:mx-96">
        @if ($previous)
            <a href="{{ route('orders.show', ['order' => $previous]) }}"
                class="btn btn-primary {{ $previous == $order->id ? 'disabled' : '' }}">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif
        <span class="h6">{{ $order->POS_number }}</span>
        @if ($next)
            <a href="{{ route('orders.show', ['order' => $next]) }}"
                class="btn btn-primary {{ $next == $order->id ? 'disabled' : '' }}">
                <i class="fas fa-chevron-right"></i>
            </a>
            @else
            <div class=""></div>
        @endif
    </div>
@endsection
