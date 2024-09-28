<div class="btn-group ">
    @if ($order->POS_number != null)
        <a href="" class="btn btn-dark btn-sm p-0 my-0 align-middle disabled ">
            <i class="fas fa-print text-xs"></i>
        </a>
        <a href="{{ route('orders.print', $order) }}" class="btn btn-outline-primary btn-sm py-0 my-0 px-1 align-middle">
            <span class="text-sm font-bold">Pdf</span>
        </a>
        <a href="{{ route('orders.print.POS', $order) }}" class="btn btn-outline-dark btn-sm py-0 my-0 px-1 align-middle">
            <span class="text-sm font-bold">POS</span>
        </a>
        @if ($order->state != 'closed')
            <a href="{{ route('orders.print.QT', $order) }}"
                class="btn btn-outline-danger btn-sm py-0 my-0 px-1 align-middle">
                <span class="text-sm font-bold">KoT</span>
            </a>
        @endif
    @endif
</div>
