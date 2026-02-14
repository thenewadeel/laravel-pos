@extends('layouts.admin')

@section('title')
    {{ 'Order Edit (Vue)' }}
@endsection

@section('content-header')
    <div class="flex flex-row justify-between md:justify-start space-x-2">
        <span class="mr-4">
            {{ 'Order:Edit (Vue)' }}
        </span>
    </div>
@endsection

@section('content-actions')
    <div class="flex flex-row justify-end space-x-2">
        <a href="{{ route('orders.show', $order) }}" class="btn btn-info btn-sm">
            <i class="nav-icon fas fa-eye"></i>
            {{ __('order.Show') }}
        </a>
        <a href="{{ route('orders.index') }}" class="btn btn-dark btn-sm">
            <i class="nav-icon fas fa-list"></i>
            {{ __('order.title_Short') }}
        </a>
    </div>
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    {{-- Vue Order Edit Component --}}
    <div id="order-edit-app">
        <order-edit
            :order="{{ json_encode($order->load(['items.product', 'customer', 'discounts', 'shop.categories'])) }}"
            :user="{{ json_encode(auth()->user()) }}"
            :user-shops="{{ json_encode(auth()->user()->shops) }}"
            :categories="{{ json_encode($categories) }}"
            :discounts="{{ json_encode($discounts) }}"
            :customers="{{ json_encode($customers) }}"
            @print-order="handlePrintOrder"
            @process-payment="handleProcessPayment"
            @cancel-order="handleCancelOrder"
        />
    </div>
@endsection

@section('js')
    @vite(['resources/js/order-edit.js'])
    <script>
        // Simple handlers for navigation actions
        function handlePrintOrder(event) {
            const orderId = event.detail;
            window.open(`/orders/${orderId}/print`, '_blank');
        }

        function handleProcessPayment(event) {
            const orderId = event.detail;
            window.location.href = `/orders/${orderId}/payment`;
        }

        function handleCancelOrder(event) {
            const orderId = event.detail;
            window.location.href = "{{ route('orders.index') }}";
        }

        // Attach event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const appContainer = document.getElementById('order-edit-app');
            appContainer.addEventListener('print-order', handlePrintOrder);
            appContainer.addEventListener('process-payment', handleProcessPayment);
            appContainer.addEventListener('cancel-order', handleCancelOrder);
        });
    </script>
@endsection