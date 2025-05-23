@extends('layouts.model.index')

@section('title')
    {{ 'Category Index' }}
@endsection
@section('content-header')
    {{ 'Cat:Index' }}
@endsection
@section('content-actions')
    <a href="{{ route('categories.create') }}" class="btn btn-primary">Create Category</a>
@endsection
@section('variables')
    @php($tableHeaders = ['Id', 'Name', 'Description', 'Image', 'Kitchen Printer IP', 'Products', 'Actions'])
    @php($varValue = 'test-value')
    @php($varData = ['test' => 'data'])
@endsection

@section('route-index', route('categories.index'))

@section('table-rows')

    @foreach ($categories as $category)
        <tr>
            {{-- {{ $category }} --}}
            <td>{{ $category['id'] }}</td>
            <td><a href="{{ route('categories.show', $category['id']) }}">{{ $category['name'] }}</a></td>

            <td>{{ $category['description'] }}</td>
            <td>
                <img width="50" src="{{ $category['image'] }}" alt="">
            </td>
            <td>{{ $category['kitchen_printer_ip'] }}</td>
            <td class="max-w-60">
                <ul class="max-h-48 overflow-y-scroll p-2 shadow-inner shadow-slate-500 rounded-md">
                    @foreach ($category['items'] as $product)
                        <li class="flex flex-row justify-between">
                            <a href="{{ route('products.edit', $product['id']) }}">{{ $product->name }}</a>
                            <span
                                class="text-xs">({{ config('settings.currency_symbol') }}{{ number_format($product->price, 0) }})</span>
                        </li>
                    @endforeach
                </ul>
            </td>
            <td>
                <a href="{{ route('categories.edit', $category['id']) }}" class="btn btn-primary"><i
                        class="fas fa-edit"></i></a>
                <button class="btn btn-danger btn-delete" data-url="{{ route('categories.destroy', $category['id']) }}"><i
                        class="fas fa-trash"></i></button>
            </td>
        </tr>
    @endforeach

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
