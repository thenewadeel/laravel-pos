<div class="card p-0 m-0 h-full overflow-y-scroll ">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        {{ __('order.Order_Details') }}
    </div>
    <div class="card-body p-1 m-0 overflow-y-scroll" wire:loading.class="bg-red-200">
        <div class="form-group p-0 m-0">
            {{-- {{ $message }} --}}
            {{-- <label for="items" class="font-weight-bold">Items:</label> --}}
            <table class="table table-striped  table-sm p-0 m-0">
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
                        <tr class="" title="{{ $item}}">

                            <td class="align-middle">{{ $item->product->name ??$item->product_name }}</td>
                            <td class="align-middle text-right">{{ $item->product->price ?? $item->product_rate }}</td>
                            <td class="align-middle">
                                <div
                                    class="text-center px-0 py-2 flex flex-row items-center overflow-hidden border-2 border-slate-200 w-max rounded-lg ">
                                    @if (auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
                                        {{-- <div class="flex overflow-hidden border w-max rounded-lg"> --}}
                                        <button type="button"
                                            class="flex items-center justify-center w-6 h-6 font-semibold rounded-r-full rounded-l-md bg-red-200 border-2 border-red-500 align-middle"
                                            wire:click="decreaseQty({{ $item->id }})">
                                            -
                                        </button>
                                    @endif
                                    <span
                                        class="bg-transparent flex items-center justify-center w-8 h-6 font-semibold text-gray-800 text-base align-middle">
                                        {{ $item->quantity }}
                                    </span>
                                    <button type="button"
                                        class="flex items-center justify-center w-6 h-6 font-semibold rounded-l-full rounded-r-md bg-green-300 border-2 border-green-500 align-middle"
                                        wire:click="increaseQty({{ $item->id }})">
                                        +
                                    </button>
                                </div>
                            </td>
                            <td class="text-right align-middle">
                                {{ number_format($item->quantity * $item->product->price, 0) }}
                            </td>
                            <td class="align-middle">
                                <button wire:click="deleteItem({{ $item->id }})"
                                    class="btn btn-danger btn-sm align-middle">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    @if(auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
                    <tr>
                        <td colspan="5">
                            <div class="flex flex-row">
                                <div class="flex flex-col">
                                    <input wire:model="miscProductName" placeholder="misc item" class="form-control">

                                    @if ($errors->has('miscProductName'))
                                        <span>{{ $errors->first('miscProductName') }}</span>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <input wire:model="miscProductPrice" placeholder="Amount" class="form-control"
                                        type="number" step="0.1">
                                    @if ($errors->has('miscProductPrice'))
                                        <span>{{ $errors->first('miscProductPrice') }}</span>
                                    @endif
                                </div>
                                <button wire:click="addMiscProduct" class="btn btn-sm btn-primary">Add</button>
                            </div>
                        </td>
                    </tr>
                    @endif
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
    @if (auth()->user()->type == 'cashier' || auth()->user()->type == 'admin')
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
    @endif
</div>
