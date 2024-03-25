@extends('layouts.model.edit')

@section('title')
    {{ 'Order Edit' }}
@endsection
@section('content-header')
    {{ 'Order:Edit' }}
@endsection
@section('content-actions')
    <div class="mb-2">
        <a href="{{ route('orders.index') }}" class="btn btn-primary">{{ __('order.Index') }}</a>
    </div>
@endsection

@section('variables')
    @php($modelName = 'Order')
    @php($modelObject = $order)
@endsection

@section('route-update', route('orders.update', ['order' => $order->id]))

@section('form-fields')


    <div class="form-group">
        <label for="shop_id" class="font-weight-bold">Shop:</label>
        <select name="shop_id" id="shop_id" class="form-control">
            @foreach ($shops as $shop)
                <option value="{{ $shop->id }}" {{ $shop->id == $order->shop_id ? 'selected' : '' }}>
                    {{ $shop->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="customer_id" class="font-weight-bold">Customer:</label>
        <select name="customer_id" id="customer_id" class="form-control">
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" {{ $customer->id == $order->customer_id ? 'selected' : '' }}>
                    {{ $customer->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="table_number" class="font-weight-bold">Table #:</label>
        <input type="number" name="table_number" id="table_number" class="form-control"
            value="{{ old('table_number', $order->table_number) }}">
    </div>
    <div class="form-group">
        <label for="waiter_name" class="font-weight-bold">Waiter:</label>
        <input type="text" name="waiter_name" id="waiter_name" class="form-control"
            value="{{ old('waiter_name', $order->waiter_name) }}">
    </div>
    <div class="form-group">
        <label for="type" class="font-weight-bold">Type:</label>
        <select name="type" id="type" class="form-control">
            <option value="dine-in" {{ $order->type == 'dine-in' ? 'selected' : '' }}>Dine-in</option>
            <option value="take-away" {{ $order->type == 'take-away' ? 'selected' : '' }}>Take Away</option>
            <option value="delivery" {{ $order->type == 'delivery' ? 'selected' : '' }}>Delivery</option>
        </select>
    </div>
    <div class="form-group">
        <label for="user_id" class="font-weight-bold">Taken By - User:</label>
        <select name="user_id" id="user_id" class="form-control">
            @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ $user->id == $order->user_id ? 'selected' : '' }}>
                    {{ $user->getFullname() }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="items" class="font-weight-bold">Items:</label>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->product->price }}</td>
                        <td>
                            <input type="number" name="items[{{ $item->id }}][quantity]"
                                value="{{ $item->quantity }}" class="form-control">
                        </td>
                        <td>{{ number_format($item->quantity * $item->product->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- {{ $order }} --}}
    <div class="form-group">
        <label for="payments" class="font-weight-bold">Payments:</label>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Received By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at }}</td>
                        <td>{{ $payment->amount }}</td>
                        <td>{{ $payment->user->getFullName() }}</td>
                        <td>
                            <form method="post" action="{{ route('orders.payments.destroy', [$order, $payment]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


@endsection
@section('footer-actions')
    <form id="payment-form" method="post" action="{{ route('orders.payments.store', $order) }}">
        <div class="form-inline">
            <label for="amount" class="mr-2">Amount:</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-control mr-2" required>
            <button form="payment-form" type="submit" class="btn btn-primary">Add Payment</button>
        </div>

        @csrf
    </form>
@endsection
