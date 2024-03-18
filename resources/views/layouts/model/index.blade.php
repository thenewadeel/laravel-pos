@extends('layouts.admin')


@section('title')
    Index - @(yield 'title')
@endsection
@section('content-header')
    @(yield 'modelName')
@endsection
@section('content-actions')
    @yield('content-actions')
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])
    <div class="card shop-list">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        @foreach ($tableHeaders as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @yield('table-rows')
                </tbody>
            </table>
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
                    title: {{ __('shop.sure') }},
                    text: {{ __('shop.really_delete') }},
                    icon: {{ __('shop.Create_Shop') }} 'warning',
                    showCancelButton: true,
                    confirmButtonText: {{ __('shop.yes_delete') }},
                    cancelButtonText: {{ __('shop.No') }},
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
