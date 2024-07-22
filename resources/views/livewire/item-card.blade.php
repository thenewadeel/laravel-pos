<div class="p-1 min-w-[15em] max-w-[18em] w-full overflow-ellipsis ">
    <div
        class="border-2  rounded-lg border-gray-200 flex flex-col h-full justify-between shadow-[2px_2px_3px] shadow-gray-400 hover:shadow-green-500 transition-all duration-500">
        <div class=" p-0 m-0  bg-gray-200 text-center font-extrabold font-serif text-md py-1 h-max">
            {{ $product->name }}
            @if ($currentQuantity)
                <span
                    class=" bg-red-500 border-2 border-white  rounded-full px-1 text-md font-bold text-white">{{ $currentQuantity }}</span>
            @endif
        </div>
        @if ($product->description)
            <div class=" p-0 m-0 text-ellipsis " title="{{ $product->description }}">
                {{ $product->description }}
            </div>
        @endif
        <div class=" flex flex-row  m-0 justify-evenly items-center p-2">

            <div class=" flex flex-row  p-0 m-0 items-center align-middle">

                <div class=" p-0 px-2 m-0 font-bold place-self-center"> Qty:</div>
                <div
                    class="text-center px-0 py-1 flex flex-row items-center overflow-hidden border-2 border-slate-200 w-max rounded-lg ">
                    <button type="button"
                        class="flex items-center justify-center w-6 h-6 font-semibold rounded-r-full rounded-l-md bg-red-200 border-2 border-red-500 align-middle"
                        wire:click="qtyDown">
                        -
                    </button>
                    <input
                        class="bg-transparent flex items-center justify-center w-12 h-6 font-semibold text-gray-800 text-base align-middle text-center"
                        wire:model.live="quantity" />
                    <button type="button"
                        class="flex items-center justify-center w-6 h-6 font-semibold rounded-l-full rounded-r-md bg-green-300 border-2 border-green-500 align-middle"
                        wire:click="qtyUp">
                        +
                    </button>
                </div>
            </div>
            <div
                class="text-right p-1 m-0 justify-around font-bold  align-middle rounded-md border-2 border-slate-200 bg-slate-50 shadow-md">
                {{ config('settings.currency_symbol') }}
                {{ number_format($product->price, 0) }}</div>
        </div>
        <div class="p-0 px-2 m-0 rounded-md mb-2" wire:loading.class="bg-red-200">
            <button type="button" wire:click="addProductToOrder" class="btn btn-outline-primary btn-block btn-sm "
                wire:loading.attr="disabled">
                <i class="fas fa-add p-0 m-0"></i>Add
            </button>
        </div>
    </div>
</div>
