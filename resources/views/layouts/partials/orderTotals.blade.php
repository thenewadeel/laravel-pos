<div class="card px-0 m-0">

    <div class="flex items-center card-header text-bold py-0 my-0 text-center">
        <div class="col-md-4">
            Total<br />
            {{ config('settings.currency_symbol') }}{{ number_format($order->total(), 0) }}
        </div>
        <div class="col-md-4">
            Discount<br />
            {{ config('settings.currency_symbol') }}{{ number_format($order->discountAmount(), 0) }}
        </div>
        <div class="col-md-4">
            Net Payable <br />
            {{ config('settings.currency_symbol') }}{{ number_format($order->balance(), 0) }}
        </div>
    </div>

</div>
