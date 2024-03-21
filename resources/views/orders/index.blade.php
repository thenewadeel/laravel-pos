@extends('layouts.admin')

@section('title', __('order.Orders_List'))
@section('content-header', __('order.Orders_List'))
@section('content-actions')

    <a href="{{ route('orders.index', ['unpaid' => true]) }}" class="btn btn-info">{{ __('order.Unpaid_Orders') }}</a>
    <a href="{{ route('orders.index', ['chit' => true]) }}" class="btn btn-warning">{{ __('order.Chit_Orders') }}</a>
    <a href="{{ route('orders.index', ['discounted' => true]) }}"
        class="btn btn-secondary">{{ __('order.Discounted_Orders') }}</a>
    <a href="{{ route('cart.index') }}" class="btn btn-primary">{{ __('cart.title') }}</a>
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        {{-- {{var_dump($orders)}} --}}
        <div class="card-body">
            <div class="row">
                <div class="col-md-7"></div>
                <div class="col-md-5">
                    <form action="{{ route('orders.index') }}">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ request('start_date') }}" />
                            </div>
                            <div class="col-md-5">
                                <input type="date" name="end_date" class="form-control"
                                    value="{{ request('end_date') }}" />
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-primary" type="submit">{{ __('order.submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('order.ID') }}</th>
                        <th>{{ 'Shop' }}</th>
                        <th>{{ __('order.Customer_Name') }}</th>
                        <th>{{ __('order.User_Name') }}</th>
                        <th>{{ __('order.Total') }}</th>
                        <th>{{ __('order.Discounts') }}</th>
                        <th>{{ __('order.DiscountAmount') }}</th>
                        <th>{{ __('order.Received_Amount') }}</th>
                        <th>{{ __('order.Status') }}</th>
                        <th>{{ __('order.To_Pay') }}</th>
                        <th>{{ __('order.Created_At') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->shop->name ?? 'Unknown' }}</td>
                            <td>{{ $order->getCustomerName() }}</td>
                            <td>{{ $order->getUserName() }}</td>
                            <td>{{ config('settings.currency_symbol') }} {{ $order->formattedTotal() }}</td>
                            <td>
                                @if ($order->discounts()->count() == 0)
                                    {{ 'None' }}
                                @else
                                    @foreach ($order->discounts()->get() as $discount)
                                        {{ $discount->name }} ({{ $discount->percentage }}%),
                                    @endforeach
                                @endif
                            </td>
                            <td style="text-align:right;">

                                {{ config('settings.currency_symbol') }} {{ number_format($order->discountAmount(), 2) }}
                            </td>
                            <td>{{ config('settings.currency_symbol') }} {{ $order->formattedReceivedAmount() }}</td>
                            <td>
                                @if ($order->receivedAmount() == 0)
                                    <span class="badge badge-danger">{{ __('order.Not_Paid') }}</span>
                                @elseif($order->receivedAmount() < $order->total())
                                    <span class="badge badge-warning">{{ __('order.Partial') }}</span>
                                @elseif($order->receivedAmount() == $order->total())
                                    <span class="badge badge-success">{{ __('order.Paid') }}</span>
                                @elseif($order->receivedAmount() > $order->total())
                                    <span class="badge badge-info">{{ __('order.Change') }}</span>
                                @endif
                            </td>
                            <td>{{ config('settings.currency_symbol') }}
                                {{ number_format($order->balance(), 2) }}
                            </td>
                            <td>{{ $order->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                        <th></th>
                        <th></th>
                        <th>{{ config('settings.currency_symbol') }} {{ number_format($receivedAmount, 2) }}</th>
                        <th></th>
                        <th>{{ config('settings.currency_symbol') }}
                            {{ number_format(
                                $orders->sum(function ($order) {
                                    return $order->balance();
                                }),
                                2,
                            ) }}
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
            {{ $orders->render() }}
        </div>
    </div>
@endsection
