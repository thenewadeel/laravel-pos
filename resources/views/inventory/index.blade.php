@extends('layouts.admin')

@section('title', __('inventory.Inventory_List'))
@section('content-header', __('inventory.Inventory_List'))
@section('content-actions')
    <div>
        <form method="GET" class="form-inline ml-3" action="{{ route('inventory.index') }}">
            @csrf
            <div class="input-group input-group-sm">
                <input type="search" name="search" class="form-control form-control-navbar"
                    placeholder="{{ __('inventory.Search_Inventory') }}" value="{{ request('search') }}">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-navbar">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('css')
    <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card inventory-list">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('inventory.ID') }}</th>
                        <th>{{ __('product.Name') }}</th>
                        <th>{{ __('product.Description') }}</th>
                        <th>{{ __('inventory.Quantity') }}</th>
                        <th>{{ __('inventory.Quantity_Type') }}</th>
                        <th>{{ __('inventory.Created_At') }}</th>
                        <th>{{ __('Last Update') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inventoryItems as $inventory)
                        <tr>
                            <td title="{{ $inventory }}">{{ $inventory->id }}</td>
                            <td>{{ $inventory->name }}</td>
                            <td>{{ $inventory->description }}</td>
                            <td>{{ $inventory->qty }}</td>
                            <td>{{ $inventory->qty_type === 'number' ? __('common.Number') : __('common.Weight') }}</td>
                            <td>{{ $inventory->created_at }}</td>
                            <td>{{ $inventory->updated_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- {{ $inventoryItems->links() }} --}}
        </div>
    </div>
@endsection
