<div>
    {{-- {{ $order }} --}}
    <div wire:loading.class="bg-red-200">

        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="text" id="amount" name="amount" class="form-control" required
                wire:model.live="customerPayment">
        </div>
        <span> {{ config('settings.currency_symbol') }} {{ $order->balance() }}</span>
        <button wire:click.prevent="checkPayment" class="btn btn-primary">Pay Now</button>
    </div>
    @if ($showModal)
        <div
            class="fixed inset-0 p-4 flex flex-wrap justify-center items-center w-full h-full z-[1000] before:fixed before:inset-0 before:w-full before:h-full before:bg-[rgba(0,0,0,0.5)] overflow-auto font-[sans-serif]">
            <div class="w-full max-w-lg bg-white shadow-lg rounded-md p-6 relative">
                <div class="flex items-center pb-3 border-b text-black" wire:click="$set('showModal', false)">
                    <h3 class="text-xl font-bold flex-1">{{ $modal_title }}</h3>
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-3.5 ml-2 cursor-pointer shrink-0 fill-black hover:fill-red-500"
                        viewBox="0 0 320.591 320.591">
                        <path
                            d="M30.391 318.583a30.37 30.37 0 0 1-21.56-7.288c-11.774-11.844-11.774-30.973 0-42.817L266.643 10.665c12.246-11.459 31.462-10.822 42.921 1.424 10.362 11.074 10.966 28.095 1.414 39.875L51.647 311.295a30.366 30.366 0 0 1-21.256 7.288z"
                            data-original="#000000"></path>
                        <path
                            d="M287.9 318.583a30.37 30.37 0 0 1-21.257-8.806L8.83 51.963C-2.078 39.225-.595 20.055 12.143 9.146c11.369-9.736 28.136-9.736 39.504 0l259.331 257.813c12.243 11.462 12.876 30.679 1.414 42.922-.456.487-.927.958-1.414 1.414a30.368 30.368 0 0 1-23.078 7.288z"
                            data-original="#000000"></path>
                    </svg>
                </div>
                <div class="my-6">
                    <p class="text-base font-bold">{{ $modal_message }}</p>
                    @if ($change)
                        Amount :
                        <span class="text-red-500 font-extrabold text-lg">
                            {{ $change }}
                        </span>
                    @endif
                </div>
                <div class="border-t flex justify-end pt-6 space-x-4">
                    <button type="button"
                        class="px-6 py-2 rounded-md text-black text-sm border-none outline-none bg-gray-200 hover:bg-gray-300 active:bg-gray-200"
                        wire:click="$set('showModal', false)">
                        {{ __('order.No_Pay') }}
                    </button>
                    <button type="button"
                        class="px-6 py-2 rounded-md text-white text-sm border-none outline-none bg-blue-600 hover:bg-blue-700 active:bg-blue-600"
                        wire:click="payAndClose">
                        {{ __('order.Yes_Pay') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
