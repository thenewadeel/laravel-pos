<div class="p-1  flex flex-col">
    <div
        class="border-2  rounded-lg border-gray-200 flex flex-col h-full justify-between shadow-[2px_2px_3px] shadow-gray-400 hover:shadow-green-500 transition-all duration-500">
        <div class="p-0 m-0  bg-gray-200 text-center font-extrabold font-serif text-md py-1 h-max">
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
            <div
                class="text-right p-1 m-0 justify-around font-bold  align-middle rounded-md border-2 border-slate-200 bg-slate-50 shadow-md">
                {{ config('settings.currency_symbol') }}
                {{ number_format($product->price, 0) }}</div>
        </div>
        <div class="p-0 px-2 m-0 rounded-md mb-2 flex flex-row items-center justify-evenly" wire:loading.class="bg-red-200">
            <button type="button" wire:click="addProductToOrder" class="border-2 border-green-100 rounded-full w-8 h-8"
                wire:loading.attr="disabled">
                +1
            </button>
            <button type="button" wire:click="addProductToOrder(2)" class="border-2 border-green-200 rounded-full w-8 h-8"
                wire:loading.attr="disabled">
                +2
            </button>
            <button type="button" wire:click="addProductToOrder(3)" class="border-2 border-green-300 rounded-full w-8 h-8"
                wire:loading.attr="disabled">
                +3
            </button>
            <button type="button" wire:click="addProductToOrder(4)" class="border-2 border-green-400 rounded-full w-8 h-8"
                wire:loading.attr="disabled">
                +4
            </button>
            <button type="button" wire:click="addProductToOrder(5)" class="border-2 border-green-500 rounded-full w-8 h-8"
                wire:loading.attr="disabled">
                +5
            </button>
        </div>
    </div>
</div>
