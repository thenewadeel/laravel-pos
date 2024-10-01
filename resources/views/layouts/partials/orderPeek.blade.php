@if ($asTable ?? false)
    <tr>
        <td>
            <a href="{{ route('orders.show', ['order' => $order->id]) }}">
                {{ $order->POS_number }}
            </a>
        </td>
        <td>{{ $order->shop?->name }}</td>
        <td class="text-right">{{ config('settings.currency_symbol') }}{{ number_format($order->balance(), 0) }}</td>
        <td class="text-right">{{ config('settings.currency_symbol') }}{{ number_format($order->receivedAmount(), 0) }}
        </td>
    </tr>
@else
    <div class=" px-0 m-0">
        <div class="flex flex-row justify-between w-full ">
            <div class="flex flex-row justify-evenly">
                <a href="{{ route('orders.show', ['order' => $order->id]) }}">
                    {{ $order->POS_number }}
                </a>
            </div>
            <div class="flex flex-row justify-evenly">
                {{ $order->shop?->name }}
            </div>
            <div class="">
                Net Payable:
                {{ config('settings.currency_symbol') }}{{ number_format($order->balance(), 0) }}
            </div>
            <div class="">
                Received:
                {{ config('settings.currency_symbol') }}{{ number_format($order->receivedAmount(), 0) }}
            </div>
        </div>
    </div>
@endif
