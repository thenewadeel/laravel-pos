<div>
    @if ($order->POS_number)
        <span class="text-base" title="{{ $order }}">{{ $order->POS_number }}</span>
    @else
        <div class="flex flex-row items-center">
            <div title="{{ $order }}"
                class="border-2 border-gray-500 text-base py-1 px-2 rounded-md  hover:bg-green-100 transition-all duration-300"
                wire:click="saveOrder" wire:loading.attr="disabled" wire:loading.class="text-red-500 ">
                <i class="fas fa-save text-xs mr-2"></i>
                Save
            </div>
            @if (auth()->user()->type == 'admin')
                <form action="{{ route('orders.destroy', [$order]) }}" method="post"
                    class=" border-2 border-red-500 text-base py-1 px-2 rounded-md  hover:bg-red-100 mx-2 text-red-600">
                    @csrf
                    @method('DELETE')
                    <button type="submit">
                        <i class="fa fa-trash"></i>
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
