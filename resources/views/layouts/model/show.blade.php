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
    <div class="container ">
        <h3>Displaying {{ $modelName ? $modelName : 'Model' }} : {{ $modelObject->name }}</h3>
        <div class="row flex flex-row  max-w-md">
            <div class="px-4">
                <label for="created_at">Date</label>
            </div>
            <div class="flex items-center">

                @yield('content-details')
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <strong>{{ __('common.Actions') }}</strong>
            </div>
            <div class="col-md-10">
                @yield('content-actions')
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
                        title: {{ __('common.sure') }},
                        text: {{ __('common.really_delete') }},
                        icon: {{ __('common.Create') }},
                        showCancelButton: true,
                        confirmButtonText: {{ __('common.yes_delete') }},
                        cancelButtonText: {{ __('common.No') }},
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
