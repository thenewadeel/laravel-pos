<div class="card p-0 m-0 overflow-y-scroll">
    <div class="card-header p-1 m-0 text-lg font-bold text-center flex flex-row justify-between items-center">
        Categories & Product List
        @include('layouts.partials.productSearch', [])
    </div>
    <div class="card-body p-1 m-0">
        {{-- <div class=""> --}}
        {{-- <label for="items" class="font-weight-bold">Items:</label> --}}
        @php($categories = $categories ? $categories : AliBayat\LaravelCategorizable\Category::all())
        @if (count($categories) > 0)
            @foreach ($categories as $category)
                <div class="card p-0 m-0">
                    <a class=" text-lg m-0 p-0 font-bold bg-blue-100 text-black"
                        onclick="toggleCat('{{ $category->id }}')">
                        {{ $category->name }}
                    </a>
                    <div class="overflow-scroll card-body p-0 m-0 flex row w-100 max-h-[37svh]"
                        id="cat-items-{{ $category->id }}"
                        @if ($loop->first) style="display: flex-wrap"
                        @else
                            style="display: none" @endif>
                        @forelse (AliBayat\LaravelCategorizable\Category::find($category->id)->entries(App\Models\Product::class)->get() as $product)
                            {{-- <div class=" border-2 border-lime-800"> --}}

                            <livewire:itemCard :product="$product" :order="$order" />
                            {{-- @include('layouts.partials.itemCard', [
                                'order' => $order,
                                'product' => $product,
                                ]) --}}
                            {{-- </div> --}}
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
    <script>
        function toggleCat(id) {
            let visibility = document.getElementById('cat-items-' + id);
            visibility.style.display = visibility.style.display === 'none' ? 'flex' : 'none';
        }
    </script>
</div>

{{-- </div> --}}
</div>
{{-- <div class="card-footer">{{ $categories }}</div> --}}
</div>
@section('js')
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script> --}}
@endsection
