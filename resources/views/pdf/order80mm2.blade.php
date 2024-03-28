<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order Receipt</title>
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}
</head>
<style>
    * {
        padding: 0px;
        margin: 0px;
        font-size: 1rem;
        font-weight: bolder;
        /* font-family:  */
    }

    .itemtable {
        /* border: 0.5px solid green; */
    }

    .itemtable td,
    .itemtable th {
        border: 0.8px solid grey;
        /* border-left-width: 0.5px; */
        border-color: black;
    }
</style>

<body style="max-width: 800px;padding: 0.5px;" class="font-sans">
    {{-- <img src="{{ asset('img/company-logo.png') }}" alt="{{ config('app.name') }} Logo" class="logo"> --}}
    {{-- <img src="{{ public_path('images/logo_blk.jpg') }}"> --}}
    <header>
        <div style=";;;;display:flex;;margin-top:0rem;">
            <table style=";;;;width:100%;">
                <tr>
                    <td style=";;;;text-align:left;width:60px;">
                        <img src="{{ public_path('images/logo_blk.jpg') }}"
                            style=";;;;border-radius:50%;width:3.5rem;height:3.5rem;display:block;" alt="Logo">
                    </td>
                    <td style=";;;;text-align:left;">
                        <div style=";;;;font-size:1.4rem;margin:0rem;font-weight:bold;overflow:hidden">
                            {{ config('app.companyName') }}
                        </div>
                        <div style=";;;;font-size:1.0rem;margin:0rem;text-align:left;">
                            {{ config('app.address', 'Address') }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <hr style=";;;;border:none;border-top:3px double rgb(0 0 0 / 35%);margin:0.15rem 0;">
    </header>

    <div class="container">
        <div style=";;;;text-align: center"><span style=";;;;font-weight: bold;">Order Receipt</span> for
            <span>order #
                {{ $order->POS_number }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Date:</span>
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
            <span>Cashier:</span>
            <span>{{ $order->user->getFullname() }}</span>
        </div>
        <table class="itemtable" style=";;;;border-collapse: collapse; border-spacing: 0;">
            <tr style=";;;;text-align:left">
                <th>Item</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Amount</th>
            </tr>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td style=";;;;text-align:right">{{ number_format($item->product->price) }}
                    </td>
                    <td style=";;;;text-align:center">{{ $item->quantity }}</td>
                    <td style=";;;;text-align:right">{{ number_format($item->price, 2) }}</td>
                </tr>
            @endforeach

            <tr>
                <th colspan="2">Total</th>
                <td style=";;;;text-align:right" colspan="2">
                    {{ config('settings.currency_symbol') }}{{ number_format($order->total(), 2) }}</td>
            </tr>

            <tr>
                <th colspan="2">
                    <span style=";;;;">Discounts :</span>
                    <span style=";;;;">
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
                <td style=";;;;text-align:right" colspan="2">
                    {{ config('settings.currency_symbol') }}{{ number_format($order->discountedTotal(), 2) }}</td>
            </tr>

            <tr>
                <th colspan="2">Received Amount</th>
                <td style=";;;;text-align:right" colspan="2">
                    {{ config('settings.currency_symbol') }}{{ $order->formattedReceivedAmount() }}</td>
            </tr>

        </table>
    </div>

    <footer>
        <p class="text-center" style=";;;;">
            Quetta Club welcomes you with warm hospitality.
            Thank you for visiting!
        </p>
        <p class="text-center" style=";;;;font-style: italic;">
            Bill generated by {{ $order->user->getFullname() }} on {{ $order->shop->name }} at
            {{ $order->created_at->format('d M-y h:i:s A') }}, printed on {{ now() }}
        </p>
    </footer>

</body>

</html>
