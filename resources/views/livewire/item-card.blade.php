<div class="p-1 col-6 col-md-4 col-lg-3 h-48 max-h-48 w-full overflow-ellipsis">
    <div class="border-2  rounded-lg border-gray-200 flex flex-col h-full justify-between">
        {{-- <div wire:loading>
        Saving post...
        {{ $message }}
    </div> --}}
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
        {{-- <div class="p-0 m-0 flex flex-row"> --}}
        <div class="text-right  p-0 m-0 justify-around font-bold text-lg">
            {{ config('settings.currency_symbol') }}
            {{ number_format($product->price, 0) }}</div>
        <div class=" flex flex-row  p-0 m-0 justify-around">
            {{-- <div class="flex flex-row p-0 px-0 m-0"> --}}
            <div class=" p-0 px-4 m-0"> Qty:</div>
            <input type="number" wire:model.live="quantity"
                class="form-control shadow-inner shadow-blue-500 rounded-md">
            {{-- </div> --}}
        </div>
        {{-- </div> --}}
        <div class="p-0 m-0 rounded-md" wire:loading.class="bg-red-200">
            <button type="button" wire:click="addProductToOrder"
                class="btn btn-outline-secondary btn-block btn-sm mt-1 " wire:loading.attr="disabled">
                <i class="fas fa-add p-0 m-0"></i>
            </button>
        </div>
        {{-- </form> --}}
    </div>
</div>
