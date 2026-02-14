@extends('layouts.admin')

@section('title', __('shop.Shop_List'))
@section('content-header', __('shop.Shop_List'))
@section('content-actions')
    <a href="{{ route('shops.create') }}" class="btn btn-primary">{{ __('shop.Create_Shop') }}</a>
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
                        <th>{{ __('shop.ID') }}</th>
                        <th>{{ __('shop.Name') }}</th>
                        <th>{{ __('shop.Desc') }}</th>
                        <th>{{ __('shop.Image') }}</th>
                        <th>{{ __('shop.CashierAcct') }}</th>
                        <th>{{ __('shop.Printer_IP') }}</th>
                        <th>{{ __('shop.Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($shops as $shop)
                        <tr>
                            <td data-toggle="tooltip" title="{{ $shop }} ">{{ $shop->id }}</td>
                            <td><a href="{{ route('shops.show', $shop) }}">{{ $shop->name }}</a></td>

                            <td>{{ $shop->description }}</td>
                            <td>
                                <img width="50" src="{{ $shop->image }}" alt="">
                            </td>
                            <td>
                                @foreach ($shop->users as $user)
                                    {{ $user->getFullName() }}({{ $user->email }}),
                                @endforeach
                            </td>
                            <td>{{ $shop->printer_ip }}</td>
                            <td>
                                <a href="{{ route('shops.edit', $shop) }}" class="btn btn-primary"><i
                                        class="fas fa-edit"></i></a>
                                <a href="{{ route('shops.products', $shop) }}" class="btn btn-info" title="Manage Products">
                                    <i class="fas fa-boxes"></i></a>
                                <button class="btn btn-danger btn-delete" data-url="{{ route('shops.destroy', $shop) }}"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $shops->render() }}
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
