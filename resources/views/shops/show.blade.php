@extends('layouts.admin')

@section('title', __('shop.Shop_List'))
@section('content-header', __('shop.Shop_List'))
@section('content-actions')
    {{-- @php
        $from = now()->subMonths(6)->startOfMonth()->format('Y-m-d');
        $to = now()->endOfMonth()->format('Y-m-d');
    @endphp --}}
    <form method="get" action="{{ route('shops.export', $shop) }}" class="form-inline">
        {{-- <div class="form-group mr-3">
            <label for="from">From</label>
            <input type="date" id="from" name="from" class="form-control" required value="{{ $from }}">
        </div>
        <div class="form-group">
            <label for="to">To</label>
            <input type="date" id="to" name="to" class="form-control" required value="{{ $to }}">
        </div> --}}
        <button type="submit" class="btn btn-success"><i class="fas fa-file-excel"></i> Export to Excel</button>
    </form>


@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])
    <div class="container ">
        <h3>Summary of orders: {{ $shop->name }}</h3>
        <div class="row flex flex-row  max-w-md">
            <div class="px-4">
                <label for="created_at">Date</label>
            </div>
            <div class="flex items-center">
                <form method="GET" action="{{ route('shops.show', $shop->id) }}" id="date-filter">
                    @csrf
                    <input type="hidden" name="clear_date" id="clear-date">
                    <input type="date" name="created_at" id="created_at" class="form-control"
                        value="{{ $filters['created_at'] ?? '' }}" onchange="this.form.submit()">
                </form>
                <button class="btn btn-link"
                    onclick="
                    event.preventDefault();
                    document.getElementById('clear-date').value = '1';
                    document.getElementById('date-filter').submit();
                ">Clear</button>
            </div>
        </div>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Ser</th>
                    <th>Cashier</th>
                    <th>POS No / Order No</th>
                    <th>Cash / Payments</th>
                    <th>Chit / Balance</th>
                    <th>Discount</th>
                    <th>Amount / Total</th>
                    <th>Customer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalReceivedAmount = 0;
                $totalBalance = 0;
                $totalAmount = 0;
                ?>
                @foreach ($orders as $index => $order)
                    <tr>
                        <td>{{ $index + 1 }} / {{ $order->created_at }}</td>
                        <td>{{ $order->user->first_name }}</td>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->receivedAmount() }}
                            <?php $totalReceivedAmount += $order->receivedAmount(); ?>
                        </td>
                        <td>{{ $order->balance() }}
                            <?php $totalBalance += $order->balance(); ?>
                        </td>
                        <td>{{ 'order->discount' }}</td>
                        <td>{{ $order->total() }}
                            <?php $totalAmount += $order->total(); ?>
                        </td>
                        <td>{{ $order->customer->membership_number }} /
                            {{ $order->customer->name }}</td>
                    </tr>
                @endforeach
                <tr class="font-bold">
                    <td colspan="3">Total</td>
                    <td>{{ $totalReceivedAmount }}</td>
                    <td>{{ $totalBalance }}</td>
                    <td></td>
                    <td>{{ $totalAmount }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col-md-2">
                <strong>{{ __('shop.Actions') }}</strong>
            </div>
            <div class="col-md-10">
                <a href="{{ route('shops.edit', $shop) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                <a class="btn btn-danger btn-delete" href="{{ route('shops.destroy', $shop) }}"><i
                        class="fas fa-trash"></i></a>
            </div>
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
