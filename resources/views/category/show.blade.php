@extends('layouts.model.show')

@section('title')
    {{ 'Category Show' }}
@endsection
@section('content-header')
    {{ 'Cat:Show' }}
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
    <div class="card">
        <h3>Products</h3>
        <div>
            @foreach ($category->products()->with(['product'])->get() as $product)
                <div class="d-flex align-items-center justify-content-between">
                    <a href="{{ route('products.show', $product->product->id) }}">{{ $product->product->name }}</a>
                    <form method="post"
                        action="{{ route('categories.products.delete', ['category_id' => $category->id, 'product_id' => $product->product->id]) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            @endforeach
        </div>
        <form id="delete-product-form" method="post" style="display:none">
            @csrf
            @method('DELETE')
        </form>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                @csrf
                <button type="submit" class="btn btn-primary">Add Products</button>
                <input type="hidden" name="category_id" value="{{ $category->id }}">

            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="product-filter" class="form-label">Filter Products</label>
                    <input id="product-filter" class="form-control" type="text"
                        oninput="
                    var filter = this.value.toLowerCase();
                    var list = document.getElementById('product-list');
                    var items = list.getElementsByTagName('li');
                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        var name = item.innerText.toLowerCase();
                        if (name.indexOf(filter) > -1) {
                            item.style.display = 'list-item';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                    ">
                </div>
                <ul id="product-list" class="list-group">
                    @foreach ($products as $product)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <input class="form-check-input me-2" type="checkbox" value="{{ $product->id }}"
                                    id="product-{{ $product->id }}" name="product_ids[]">
                                {{ $product->name }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </form>
    </div>
@endsection

@section('content-actions')
    <button class="btn btn-primary">Test Button</button>
@endsection
