@extends('layouts.admin')

@section('title', 'Members Index')
@section('content-header', 'Members Index')
@section('content-actions')
<div>
    <form method="GET" class="form-inline ml-3" action="{{ route('customers.index') }}">
        @csrf
        <div class="input-group input-group-sm">
            <input type="search" name="search" class="form-control form-control-navbar"
                placeholder="{{ __('customer.Search_Customers') }}" value="{{ request('search') }}">
            <div class="input-group-append">
                <button type="submit" class="btn btn-navbar">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </form>
    <a href="{{ route('customers.create') }}" class="btn btn-primary">{{ __('customer.Add_Customer') }}</a>
</div>
@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('photo') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ 'Member#' }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Address') }}</th>
                        <th>{{ __('common.Created_At') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>
                                <img width="50" src="{{ $customer->getphotoUrl() }}" alt="">
                            </td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->membership_number }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->address }}</td>
                            <td>{{ $customer->created_at }}</td>
                            <td>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary"><i
                                        class="fas fa-edit"></i></a>
                                <button class="btn btn-danger btn-delete"
                                    data-url="{{ route('customers.destroy', $customer) }}"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $customers->render() }}
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
