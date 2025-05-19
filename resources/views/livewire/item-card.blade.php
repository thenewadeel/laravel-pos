<div class="p-1 w-full sm:w-1/2 lg:w-1/3 xl:w-1/4  overflow-ellipsis">
    <div
        class="border-0  rounded-lg border-gray-200 flex flex-col h-full justify-between shadow-[2px_2px_3px] shadow-gray-400 hover:shadow-green-500 transition-all duration-500 p-0 m-0">
        <div class=" p-0 m-0  bg-gray-200 text-center font-bold font-serif  text-md py-0 pb-1 h-max rounded-t-md">
            {{ $product->name }}
            @if ($currentQuantity)
                <span
                    class=" bg-red-500 border-2 border-white  rounded-full p-0 m-0 px-1 text-md font-bold text-white">{{ $currentQuantity }}</span>
            @endif
        </div>
        @if ($product->description)
            <div class=" p-0 m-0 text-ellipsis " title="{{ $product->description }}">
                {{ $product->description }}
            </div>
        @else
            {{-- <div class=" p-0 m-0 text-ellipsis ">
                No description available
            </div> --}}
        @endif
        <div class=" flex flex-col sm:flex-row border-0 border-orange-300 m-0 justify-evenly items-center p-0">

            <div
                class=" flex flex-col sm:flex-row border-0 border-fuchsia-300 p-0 m-0 items-center align-middle justify-between">

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
            <div class="text-right py-1 m-0 font-bold rounded-md border-2 border-slate-200 bg-slate-50 shadow-md">
                {{ config('settings.currency_symbol') }}
                {{ number_format($quantity * $product->price, 0) }}</div>
        </div>
        <div class="p-0 px-2 m-0 rounded-md mb-2" wire:loading.class="bg-red-200">
            <button type="button" wire:click="addProductToOrder" class="btn btn-outline-primary btn-block btn-sm "
                wire:loading.attr="disabled">
                <i class="fas fa-add p-0 m-0"></i>Add
            </button>
        </div>
    </div>
</div>
