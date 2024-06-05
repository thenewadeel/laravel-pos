<div>
    {{-- {{ $order }} --}}
    <div wire:loading.class="bg-red-200">

        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="text" id="amount" name="amount" class="form-control" required>
        </div>
        <span> {{ config('settings.currency_symbol') }} {{ $order->balance() }}</span>
        <button wire.prevent="payNow" class="btn btn-primary">Pay Now</button>
    </div>
</div>
