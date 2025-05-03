@extends('layouts.admin')

@section('title', __('product.Product_List'))
@section('content-header', __('product.Product_List'))
@section('content-actions')
    <div>
        <form method="GET" class="form-inline ml-3" action="{{ route('products.index') }}">
            @csrf
            <div class="input-group input-group-sm">
                <input type="search" name="search" class="form-control form-control-navbar"
                    placeholder="{{ __('product.Search_Product') }}" value="{{ request('search') }}">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-navbar">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
        <a href="{{ route('products.create') }}" class="btn btn-primary">{{ __('product.Create_Product') }}</a>
    </div>
@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card product-list">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('product.ID') }}</th>
                        <th>{{ __('product.Name') }}</th>
                        <th>{{ __('product.Description') }}</th>
                        <th>{{ __('product.Price') }}</th>
                        {{-- <th>{{ 'Make' }}</th> --}}
                        <th>{{ 'Category' }}</th>
                        <th>{{ __('product.Image') }}</th>
                        {{-- <th>{{ __('product.Barcode') }}</th> --}}
                        {{-- <th>{{ __('product.Quantity') }}</th> --}}
                        <th>{{ __('product.Status') }}</th>
                        {{-- <th>{{ __('product.Created_At') }}</th> --}}
                        {{-- <th>{{ __('Last Update') }}</th> --}}
                        <th>{{ __('product.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td title="{{ $product }}">{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>
                                {{ $product->description }} <br />
                                {{--  $product->variants --}}
                                @foreach ($product->variants as $variant)
                                    {{ $variant->description }} <br />
                                @endforeach
                            </td>
                            <td>
                                {{ $product->price }} <br />
                                {{--  $product->variants --}}
                                @foreach ($product->variants as $variant)
                                    {{ $variant->price }} <br />
                                @endforeach
                            </td>
                            {{-- <td>{{ $product->description }}</td> --}}
                            <td>{{ $product->categories()->pluck('name')->implode(', ') }}</td>
                            <td><img class="product-img" src="{{ Storage::url($product->image) }}" alt=""
                                    style="width: 64px !important; height: 64px !important;"></td>
                            {{-- <td>{{ $product->barcode }}</td> --}}
                            {{-- <td>{{ $product->quantity }}</td> --}}
                            <td>
                                <span
                                    class="right badge badge-{{ $product->aval_status ? 'success' : 'danger' }}">{{ $product->aval_status ? __('common.Active') : __('common.Inactive') }}</span>
                            </td>
                            {{-- <td>{{ $product->created_at }}</td> --}}
                            {{-- <td>{{ $product->updated_at }}</td> --}}
                            <td>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary"><i
                                        class="fas fa-edit"></i></a>
                                <button class="btn btn-danger btn-delete"
                                    data-url="{{ route('products.destroy', $product) }}"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $products->render() }}
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script type="module">
        $(document).ready(function() {
            $(document).on('click', '.btn-delete', function() {
                var $this = $(this);
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                    title: '{{ __('common.sure') }}',
                    text: '{{ __('common.really_delete') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('common.yes_delete') }}',
                    cancelButtonText: '{{ __('common.No') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.post($this.data('url'), {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        }, function(res) {
                            $this.closest('tr').fadeOut(500, function() {
                                $(this).remove();
                            })
                        })
                    }
                })
            })
        })
    </script>
@endsection
