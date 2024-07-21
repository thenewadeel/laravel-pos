<div class="p-1 col-6 col-md-4 col-lg-3 h-[20vh] md:h-[18vh] w-full overflow-ellipsis ">
    <div
        class="border-2  rounded-lg border-gray-200 flex flex-col h-full justify-between shadow-[2px_2px_3px] shadow-gray-400 hover:shadow-green-500 transition-all duration-500">
        <div class=" p-0 m-0  bg-gray-200 text-center font-extrabold font-serif text-lg py-1">
            {{ $product->name }}
            @if ($currentQuantity)
                <span
                    class=" bg-red-500 border-2 border-white  rounded-full px-1 text-lg font-bold text-white">{{ $currentQuantity }}</span>
            @endif
        </div>
        @if ($product->description)
            <div class=" p-0 m-0 truncate h-6" title="{{ $product->description }}">
                {{ $product->description }}
            </div>
        @endif
        <div class="text-right  p-0 m-0 justify-around font-bold text-lg">
            {{ config('settings.currency_symbol') }}
            {{ number_format($product->price, 0) }}</div>
        <div class=" flex flex-row  p-0 m-0 justify-center">
            <div class=" p-0 px-2 m-0 font-bold place-self-center"> Qty:</div>
            <input type="number" wire:model.live="quantity"
                class="form-control shadow-inner shadow-blue-500 rounded-md md:max-w-48 max-w-24 p-0 m-0">
        </div>
        <div class="p-0 px-2 m-0 rounded-md" wire:loading.class="bg-red-200">
            <button type="button" wire:click="addProductToOrder" class="btn btn-outline-secondary btn-block btn-sm "
                wire:loading.attr="disabled">
                <i class="fas fa-add p-0 m-0"></i>Add
            </button>
        </div>
    </div>
</div>
