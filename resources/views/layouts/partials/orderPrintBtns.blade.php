<div class="btn-group ">
    <a href="" class="btn btn-dark btn-sm disabled px-0">
        <i class="fas fa-print text-xs"></i>
    </a>
    <a href="{{ route('orders.print', $order) }}" class="btn btn-outline-primary btn-sm">
        <span class="text-sm">Pdf</span>
    </a>
    <a href="{{ route('orders.print.POS', $order) }}" class="btn btn-outline-dark btn-sm">
        <span class="text-sm">POS</span>
    </a>
    <a href="{{ route('orders.print.QT', $order) }}" class="btn btn-outline-danger btn-sm">
        <span class="text-sm">KoT</span>
    </a>
</div>
