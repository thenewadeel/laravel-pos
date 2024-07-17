<div>
    @if ($order->POS_number)
        <span class="text-base" title="{{ $order }}">{{ $order->POS_number }}</span>
    @else
        <div title="{{ $order }}"
            class="border-2 border-gray-500 text-base py-1 px-2 rounded-md w-min hover:bg-green-100 transition-all duration-300 flex flex-row items-center"
            wire:click="saveOrder" wire:loading.attr="disabled" wire:loading.class="text-red-500 ">
            <i class="fas fa-save text-xs mr-2"></i>
            Save
        </div>
    @endif
</div>
