<div class="card p-0 m-0">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        Categories & Product List
    </div>
    <div class="card-body p-1 m-0">
        {{-- <div class=""> --}}
        {{-- <label for="items" class="font-weight-bold">Items:</label> --}}
        @if (count($categories) > 0)
            @foreach ($categories as $category)
                <div class="card p-0 m-0">
                    <a class="card-header text-lg m-0 p-0 font-bold">
                        {{ $category->name }}
                    </a>
                    <div class="card-body p-0 m-0 flex row w-100 max-h-80  overflow-y-scroll">
                        @forelse (AliBayat\LaravelCategorizable\Category::find($category->id)->entries(App\Models\Product::class)->get() as $product)
                            <div class="col-md-3 h-max p-1 m-0">
                                <form action="{{ route('order.items.store', $order) }}" method="post"
                                    class="border p-1 m-0">
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
                                                class="form-inline col-md-6 shadow-inner shadow-blue-500 rounded-md"
                                                value="1" min="1">
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
                                        <button type="submit"
                                            class="btn btn-outline-secondary btn-block btn-sm mt-1 "><i
                                                class="fas fa-add p-0 m-0"></i></button>
                                    </div>
                                </form>
                            </div>
                        @empty
                            <p>No items available.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        @else
            <p>No categories found.</p>
        @endif
    </div>
</div>

{{-- </div> --}}
</div>
{{-- <div class="card-footer">{{ $categories }}</div> --}}
</div>
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
