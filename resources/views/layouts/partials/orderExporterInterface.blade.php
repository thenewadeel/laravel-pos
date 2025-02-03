<div class="flex justify-center">
    <form action="{{ route('orders.exporter.burn') }}" method="GET">
        @csrf
        <button type="submit"
            class="px-4 py-2 text-white bg-red-500 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            BURN ORDERS
        </button>
    </form>
</div>
