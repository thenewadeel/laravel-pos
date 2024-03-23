<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Receipt</title>
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}
</head>

<body>
    <header>
        {{-- <img src="{{ asset('img/company-logo.png') }}" alt="{{ config('app.name') }} Logo" class="logo"> --}}
        {{-- <img src="{{ asset('images/logo_blk.jpg') }}"> --}}
        <img src="{{ public_path('images/logo_blk.jpg') }}" width="100px" alt="">
        <h1 class="title">{{ config('app.name') }}</h1>
        <h3 class="subtitle">{{ config('app.address', 'poiuytred') }}</h3>
    </header>

    <div class="container">
        <h2 class="text-center">Order Receipt</h2>
        <table class="table table-striped">
            <tr>
                <th>Order ID</th>
                <td>{{ $order->id }}</td>
            </tr>
            <tr>
                <th>Order Date</th>
                <td>{{ $order->created_at }}</td>
            </tr>
            <tr>
                <th>Customer</th>
                <td>{{ $order->customer->name }}</td>
            </tr>
            {{-- @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->pivot->quantity }}</td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($item->pivot->price, 2) }}</td>
                </tr>
            @endforeach --}}
            <tr>
                <th>Total</th>
                <td colspan="2">{{ config('settings.currency_symbol') }} {{ number_format($order->total(), 2) }}
                </td>
            </tr>
        </table>
    </div>

    <footer>
        <hr>
        <p class="text-center">Thank you for shopping with us!</p>
    </footer>
</body>

</html>
