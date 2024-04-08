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
    <div class="card p-2  d-flex flex-row">
        <div class="card-body">
            <h3>Products</h3>
            {{-- {{ $category }} --}}
            @foreach ($category->products()->get() as $product)
                <div class="d-flex align-items-center justify-content-between ">
                    {{ $product->name }}
                    <form method="post"
                        action="{{ route('categories.products.delete', ['category_id' => $category->id, 'product_id' => $product->id]) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                @csrf
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-plus-circle me-2"></i>Add
                    Products</button>
                <input type="hidden" name="category_id" value="{{ $category->id }}">

            </div>
            <div class="card-body">

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
                <div style="max-height: 60vh; overflow-y: scroll;;" class="flex grow">
                    <div class="">
                        <div class="card" id='product-list'>
                            @foreach ($products as $product)
                                {{-- <li>{{ $product->name }}</li> --}}
                                <div class="card-body d-flex justify-content-start my-0 py-0 w-max align-items-center"
                                    data-name="{{ strtolower($product->name) }}">
                                    <input class="form-check-input" type="checkbox" value="{{ $product->id }}"
                                        id="product-{{ $product->id }}" name="product_ids[]">
                                    {{ $product->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('content-actions')
    <button class="btn btn-primary">Test Button</button>
@endsection
