<div class="card p-0 m-0">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        Categories & Product List
    </div>
    <div class="card-body p-1 m-0">
        {{-- <div class=""> --}}
        {{-- <label for="items" class="font-weight-bold">Items:</label> --}}
        @php($categories = $categories ? $categories : AliBayat\LaravelCategorizable\Category::all())
        @if (count($categories) > 0)
            @foreach ($categories as $category)
                <div class="card p-0 m-0">
                    <a class="card-header text-lg m-0 p-0 font-bold">
                        {{ $category->name }}
                    </a>
                    <div class="card-body p-0 m-0 flex row w-100 max-h-80  overflow-y-scroll">
                        @forelse (AliBayat\LaravelCategorizable\Category::find($category->id)->entries(App\Models\Product::class)->get() as $product)
                            <livewire:itemCard :product="$product" :order="$order" />
                            {{-- @include('layouts.partials.itemCard', [
                                'order' => $order,
                                'product' => $product,
                            ]) --}}
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
