@extends('layouts.model.show')

@section('title')
    {{ 'Category Show' }}
@endsection
@section('content-header')
    {{-- {{ 'Cat:Show' }} --}}
@endsection
@section('content-actions')
    {{-- <button class="btn btn-primary">Test Button</button> --}}
@endsection

@section('variables')
    @php($modelName = 'Category')
    @php($modelObject = $category)
    @php($varData = ['test' => 'data'])
@endsection

@section('content-details')
    <div class="card flex flex-row max-h-screen overflow-y-scroll">
        <div class="px-3 py-14 h-auto w-auto  self-stretch text-end font-bold font-serif rounded-tr-full bg-sky-900 text-xl text-white"
            style="writing-mode: sideways-lr;">
            <a href="{{ route('categories.edit', $category) }}" class="text-white">
                {{ $category->name }}
            </a>
        </div>
        <div class="card-body overflow-y-scroll">
            <h3 class="font-bold px-4 min-w-48">Products</h3>
            {{-- {{ $category }} --}}
            @if ($products->isEmpty())
                <p class="text-center text-bold text-red-900">No products available</p>
            @endif
            @foreach ($products as $product)
                <div
                    class="flex flex-row align-items-start justify-content-between hover:shadow-md m-2 border-2 rounded-md p-2">
                    <div title="{{ $product }}" class="grow">
                        <a href="{{ route('products.edit', $product) }}">
                            {{ $product->name }}
                        </a>
                    </div>
                    <form method="post"
                        action="{{ route('categories.products.delete', ['category_id' => $category->id, 'product_id' => $product->id]) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            @endforeach
            <form id="delete-product-form" method="post" style="display:none">
                @csrf
                @method('DELETE')
            </form>
        </div>
        <script>
            function deleteProduct(id) {
                if (confirm('Are you sure you want to delete this product?')) {
                    let form = document.getElementById('delete-product-form');
                    form.action = `/api/products/${id}`;
                    form.submit();
                }
            }
        </script>
        <form method="post" action="{{ route('categories.products.store') }}">

            <div class="card-body p-2">

                <div class="form-group mb-3">
                    <label for="product-filter" class="form-label">Filter Products</label>
                    <input id="product-filter" class="form-control" type="text"
                        onkeyup="
                    var filter = this.value.toLowerCase();
                    // console.log({filter});
                    var list = document.getElementById('product-list');
                    // console.log({list});
                    var items = list.getElementsByTagName('div');
                    // console.log({items});
                    // console.log(items);
                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        var name = item.dataset.name.toLowerCase();
                        var classes='list-group-item d-flex justify-content-between align-items-center';
                        if(!(name.indexOf(filter) > -1)){
                            item.style.display = 'none';
item.className='';
                        }else{
                            item.style.display = 'list-item';
                            item.className=classes;
                        }
                    }
                    ">
                </div>
                <div class="flex grow ">
                    <div class="">
                        <div class="card" id='product-list'>
                            <div class="flex flex-row justify-content-between border-2 bg-gray-100 px-2">
                                <div class="border-2 border-sky-300 text-sky-600 mx-2 rounded-md hover:shadow-md min-w-16 text-center"
                                    onclick="selectAll()">All
                                </div>
                                <div class="text-base w-max font-bold font-sans"><-- Select --></div>
                                <div class="border-2 border-red-900 text-red-900 mx-2 rounded-md hover:shadow-xl min-w-16 text-center"
                                    onclick="selectNone()">
                                    None
                                </div>
                            </div>
                            <div class="min-h-48 max-h-96 overflow-y-scroll">

                                @foreach (App\Models\Product::doesntHave('categories')->get() as $product)
                                    {{-- <li>{{ $product->name }}</li> --}}
                                    <div class="card-body justify-content-start my-0 py-0 w-max align-items-center p-2 "
                                        data-name="{{ strtolower($product->name) }}">
                                        <input class="accent-red-500" type="checkbox" value="{{ $product->id }}"
                                            id="product-{{ $product->id }}" name="product_ids[]">
                                        {{ $product->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <script>
                            function selectAll() {
                                document.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                                    checkbox.checked = true;
                                });
                            }

                            function selectNone() {
                                document.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                                    checkbox.checked = false;
                                });
                            }
                        </script>
                    </div>
                </div>
                <div class="card-header d-flex justify-content-between align-items-center">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-plus-circle me-2"></i>Add
                        Products</button>
                    <input type="hidden" name="category_id" value="{{ $category->id }}">

                </div>
            </div>
        </form>
    </div>
@endsection

@section('content-actions')
    <button class="btn btn-primary">Test Button</button>
@endsection
