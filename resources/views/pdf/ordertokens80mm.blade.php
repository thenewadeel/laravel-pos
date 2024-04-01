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

    .tokentable {
        width: 100%;
        /* expand to max available space */
    }

    .tokentable td,
    .tokentable th {
        border: 0.8px solid grey;
        /* border-left-width: 0.5px; */
        border-color: black;
        font-weight: bold;
    }
</style>

<body style="max-width: 800px;padding: 0.5px;" class="font-sans">
    {{-- <img src="{{ asset('img/company-logo.png') }}" alt="{{ config('app.name') }} Logo" class="logo"> --}}
    {{-- <img src="{{ public_path('images/logo_blk.jpg') }}"> --}}
    @foreach ($order->items as $index => $item)
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
        <hr>
        <div class="container">
            <div style=";;;;text-align: center"><span style=";;;;font-weight: bolder;">Chaand Raat Festival 2024</span>
            </div>
            <div style="text-align: center"> Dated # {{ now() }}</div>
            <div style=";;;;text-align: left"><span style=";;;;font-weight: bold;">Order #
                    {{ $order->POS_number }}</span>
                <span style=";;;;text-align: right; display: block;"> Token#{{ $index + 1 }}</span>
            </div>
            <table class="tokentable" style=";;;;border-collapse: collapse; border-spacing: 0;">
                <tr>
                    <th>Item Name</th>
                    {{-- <th>Price</th> --}}
                    <th style="width:25%">Qty</th>
                </tr>
                <tr>
                    <td style="font-size: 20px;font-weight: bold;">{{ $item->product->name }} -
                        {{ $item->product->description }}
                        <span style="font-style: italic">@ {{ config('settings.currency_symbol') }}
                            {{ number_format($item->product->price) }}</span>
                    </td>
                    <td style=";;;;font-size: 24px;text-align:center;" class="text-center font-extrabold font-serif">
                        {{ $item->quantity }}
                    </td>
                    {{-- <td style=";;;;text-align:right">{{ number_format($item->price, 2) }}</td> --}}
                </tr>
            </table>
            <div class="d-flex justify-content-between">
                <span>Total:</span>
                <span>{{ config('settings.currency_symbol') }} {{ $item->product->price * $item->quantity }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Cashier:</span>
                <span>{{ $order->user->getFullname() }}</span>
            </div>
            <span>Date: {{ $order->created_at->format('d-m-Y h:i A') }}</span>
            <div style="page-break-after:always;"></div>
        </div>
    @endforeach
</body>

</html>
