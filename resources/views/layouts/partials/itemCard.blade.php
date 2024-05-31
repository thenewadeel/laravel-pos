<div class="col-6 col-md-6 col-lg-4 h-max p-1 m-0 min-w-48">
    <form action="{{ route('order.items.store', $order) }}" method="post" class="border p-1 m-0">
        @csrf
        <div class="row p-0 m-0 font-bold">{{ $product->name }}</div>
        <div class="row p-0 m-0">{{ $product->description }}</div>
        <div class="row p-0 m-0 justify-around">
            {{ config('settings.currency_symbol') }}
            {{ $product->price }}</div>
        <input type="hidden" name="item" value="{{ $product->id }}">
        <div class="row  p-0 m-0 justify-items-center">
            <div class="col-md-8 flex row p-0 px-0 m-0">
                <div class="col-md-4 p-0 m-0"> Qty:</div>
                <input type="number" name="quantity"
                    class="form-inline col-md-6 shadow-inner shadow-blue-500 rounded-md" value="1" min="1">
            </div>
            @if ($order->items->pluck('product.id')->contains($product->id))
                <div class="col-md-4 p-0 m-0">
                    <i class="fas fa-shopping-cart text-3xl text-sky-600"></i>
                    <span
                        class="relative -left-4 -top-4 bg-red-500 border-2 border-white  rounded-full px-1 text-lg font-bold text-white">{{ $order->items->where('product_id', $product->id)->first()->quantity }}</span>
                </div>
            @endif
        </div>
        <div class="row p-0 m-0">
            <button type="submit" class="btn btn-outline-secondary btn-block btn-sm mt-1 "><i
                    class="fas fa-add p-0 m-0"></i></button>
        </div>
    </form>
</div>
