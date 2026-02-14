@extends('layouts.admin')

@section('title')
    {{ 'Orders Workspace (Vue)' }}
@endsection

@section('content-header')
    <div class="flex flex-row justify-between md:justify-start space-x-2">
        <span class="mr-4">
            {{ 'Orders Workspace' }}
        </span>
    </div>
@endsection

@section('content-actions')
    <div class="flex flex-row justify-end space-x-2">
        <a href="{{ route('orders.index') }}" class="btn btn-dark btn-sm">
            <i class="nav-icon fas fa-list"></i>
            {{ __('order.title_Short') }}
        </a>
    </div>
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    {{-- Vue Orders Workspace Component --}}
    <div id="orders-workspace-app">
        <orders-workspace
            :initial-order="{{ json_encode($order->load(['items.product', 'customer', 'discounts', 'shop.categories'])) }}"
            :user="{{ json_encode(auth()->user()) }}"
            :user-shops="{{ json_encode(auth()->user()->shops) }}"
            :categories="{{ json_encode($categories) }}"
            :discounts="{{ json_encode($discounts) }}"
            :customers="{{ json_encode($customers) }}"
        />
    </div>
@endsection

@section('js')
    @vite(['resources/js/orders-workspace.js'])
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
            window.location.href = "{{ route('orders.index') }}";
        }

        // Attach event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const appContainer = document.getElementById('orders-workspace-app');
            appContainer.addEventListener('print-order', handlePrintOrder);
            appContainer.addEventListener('process-payment', handleProcessPayment);
            appContainer.addEventListener('cancel-order', handleCancelOrder);
        });
    </script>
@endsection