<div class="card p-0 m-0 h-full overflow-y-scroll ">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        {{ __('order.Order_Details') }}
    </div>
    <div class="card-body p-1 m-0 overflow-y-scroll" wire:loading.class="bg-red-200">
        <div class="form-group p-0 m-0">
            {{-- {{ $message }} --}}
            {{-- <label for="items" class="font-weight-bold">Items:</label> --}}
            <table class="table table-striped table-bordered table-sm p-0 m-0">
                <thead>
                    <tr class="text-center font-bold">
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="">{{ $item->product->name }}</td>
                            <td class="text-right">{{ $item->product->price }}</td>
                            <td class="text-center flex flex-row">
                                <div class="flex flex-row items-center">
                                    <i class="fas fa-minus-circle text-red-500 hover:text-red-700 cursor-pointer"
                                        wire:click="decreaseQty({{ $item->id }})"></i>
                                    &nbsp;
                                </div>
                                {{ $item->quantity }}
                                <div class="flex flex-row items-center">
                                    <i class="fas fa-plus-circle text-green-500 hover:text-green-700 cursor-pointer"
                                        wire:click="increaseQty({{ $item->id }})"></i>
                                    &nbsp;
                                </div>
                            </td>
                            {{-- <td>
                                <input type="number" name="items[{{ $item->id }}][quantity]"
                                    value="{{ $item->quantity }}" class="form-control">
                            </td> --}}
                            <td class="text-right">
                                {{ number_format($item->quantity * $item->product->price, 0) }}
                            </td>
                            <td>
                                {{-- <form action="{{ route('order.items.destroy', [$order, $item]) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form> --}}
                                <button wire:click="deleteItem({{ $item->id }})" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="text-center font-weight-bold">{{ $order->items->count() }}x Items</td>
                        <td class="text-right font-weight-bold">Total</td>
                        <td class="text-center font-weight-bold">{{ $order->items->sum('quantity') }}</td>
                        <td class="font-weight-bold text-right">
                            {{ config('settings.currency_symbol') }}
                            {{ number_format($order->total(), 0) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="flex flex-wrap p-0 m-0" wire:loading.class="bg-red-200">
        @foreach (App\Models\Discount::all() as $discount)
            <div
                class=" p-0 py-1 m-0 rounded-lg col-4 flex flex-row align-middle items-center justify-start justify-items-center">
                <div
                    class="form-check form-check-inline badge-{{ $discount->type == 'CHARGES' ? 'warning' : 'success' }} rounded-md p-0  m-0 mx-1 w-full h-12 shadow-md">
                    <input class="form-check-input mx-1 p-0 m-0 text-xl" type="checkbox" name="discountsToAdd[]"
                        style="width: 1.5rem; height: 1.5rem;" value="{{ $discount->id }}"
                        {{ in_array($discount->id, $order->discounts->pluck('id')->toArray()) ? 'checked' : '' }}
                        wire:change.live="toggleDiscount({{ $discount->id }})">
                    <label class="form-check-label m-0 p-0 text-md font-bold leading-none self-center ml-2"
                        for="discount{{ $discount->id }}">
                        {{ $discount->name }}
                        <br />
                        <span class="text-sm font-normal">
                            ({{ $discount->percentage }}%)
                        </span>
                    </label>
                </div>
            </div>
        @endforeach
    </div>
    <div class="flex flex-row items-center justify-between leading-none card-header text-bold py-0 my-0 text-center px-4"
        wire:loading.class="bg-red-200">
        <div class="">
            <span class="text-base font-normal">Total</span>
            <br />
            {{ config('settings.currency_symbol') }}{{ number_format($order->total(), 0) }}
        </div>
        <div class="">
            <span class="text-base font-normal">Discount</span>
            <br />
            {{ config('settings.currency_symbol') }}{{ number_format($order->discountAmount(), 0) }}
        </div>
        <div class="">
            <span class="text-base font-normal">Net Payable</span>
            <br />
            {{ config('settings.currency_symbol') }}{{ number_format($order->balance(), 0) }}
        </div>
    </div>
</div>
