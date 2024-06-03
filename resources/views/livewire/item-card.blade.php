{{-- <div> --}}
<div class="col-6 col-md-6 col-lg-4 h-max p-0 m-0 min-w-48 rounded-md border-2">
    {{-- <div wire:loading>
        Saving post...
        {{ $message }}
    </div> --}}
    <div class=" row p-0 m-0 font-bold">
        {{ $product->name }}
    </div>
    <div class=" row p-0 m-0 justify-between">
        {{ $product->description }}
        <div class=" row p-0 m-0 justify-around">
            {{ config('settings.currency_symbol') }}
            {{ $product->price }}</div>
    </div>
    <div class=" row  p-0 m-0 justify-items-center">
        <div class="col-md-8 flex row p-0 px-0 m-0">
            <div class="col-md-4 p-0 m-0"> Qty:</div>
            <input type="number" wire:model.live="quantity"
                class="form-inline col-md-6 shadow-inner shadow-blue-500 rounded-md">
        </div>
        @if ($currentQuantity)
            <div class="col-md-4 p-0 m-0">
                <i class="fas fa-shopping-cart text-3xl text-sky-600"></i>
                <span
                    class="relative -left-4 -top-4 bg-red-500 border-2 border-white  rounded-full px-1 text-lg font-bold text-white">{{ $currentQuantity }}</span>
            </div>
        @endif
    </div>
    <div class="p-0 m-0 rounded-md" wire:loading.class="bg-red-200">
        <button type="button" wire:click="addProductToOrder" class="btn btn-outline-secondary btn-block btn-sm mt-1 "
            wire:loading.attr="disabled">
            <i class="fas fa-add p-0 m-0"></i>
        </button>
    </div>
    {{-- </form> --}}
</div>
{{-- </div> --}}
