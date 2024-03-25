@extends('layouts.model.show')

@section('title')
    {{ 'Order Show' }}
@endsection
@section('content-header')
    {{ 'Order:Show' }}
@endsection
@section('content-actions')
    <div class="mb-2">
        <a href="{{ route('orders.index') }}" class="btn btn-primary">{{ __('order.Index') }}</a>
        <a href="{{ route('orders.edit', $order) }}" class="btn btn-primary">{{ __('order.Edit') }}</a>
    </div>
@endsection


@section('variables')
    @php($modelName = 'Order')
    @php($modelObject = $order)
@endsection

@section('content-details')
    <div class="card p-2">
        <h4>Order # {{ $order->POS_number }}</h4>
        <div class="d-flex justify-content-between">
            <span>Date Taken:</span>
            <span>{{ $order->created_at->format('d-m-Y') }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Shop:</span>
            <span>{{ $order->shop->name }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Customer:</span>
            <span>{{ $order->customer->name }}</span>
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
                    {{ config('settings.currency_symbol') }}{{ $order->formattedBalance() }}</td>
            </tr>

            <tr>
                <th colspan="2">Received Amount</th>
                <td style="text-align:right" colspan="2">
                    {{ config('settings.currency_symbol') }}{{ $order->formattedReceivedAmount() }}</td>
            </tr>

        </table>

        {{-- {{ $order }} --}}
    </div>


@endsection

@section('footer-actions')
    <div class="d-flex justify-content-between w-100">
        <a href="{{ route('orders.show', ['order' => $previous]) }}"
            class="btn btn-primary {{ $previous == $order->id ? 'disabled' : '' }}">
            <i class="fas fa-chevron-left"></i>
        </a>

        <span class="h6">{{ $order->POS_number }}</span>

        <a href="{{ route('orders.show', ['order' => $next]) }}"
            class="btn btn-primary {{ $next == $order->id ? 'disabled' : '' }}">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
@endsection
