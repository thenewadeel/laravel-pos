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
            @update-order="handleOrderUpdate"
            @add-item="handleAddItem"
            @update-item="handleUpdateItem"
            @delete-item="handleDeleteItem"
            @toggle-discount="handleToggleDiscount"
            @process-payment="handleProcessPayment"
            @cancel-order="handleCancelOrder"
            @print-order="handlePrintOrder"
        />
    </div>
@endsection

@section('js')
    <script src="{{ mix('js/order-edit.js') }}"></script>
    <script>
        // Event handlers for Vue component events
        function handleOrderUpdate(event) {
            const orderData = event.detail;
            
            fetch(`/api/v1/orders/${orderData.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Order updated successfully');
                } else {
                    toastr.error(data.error?.message || 'Failed to update order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while updating the order');
            });
        }

        function handleAddItem(event) {
            const itemData = event.detail;
            
            fetch(`/api/v1/orders/{{ $order->id }}/items`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(itemData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Item added successfully');
                    // Refresh the page to show updated items
                    window.location.reload();
                } else {
                    toastr.error(data.error?.message || 'Failed to add item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while adding the item');
            });
        }

        function handleUpdateItem(event) {
            const { itemId, ...updateData } = event.detail;
            
            fetch(`/api/v1/orders/{{ $order->id }}/items/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(updateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Item updated successfully');
                    window.location.reload();
                } else {
                    toastr.error(data.error?.message || 'Failed to update item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while updating the item');
            });
        }

        function handleDeleteItem(event) {
            const itemId = event.detail;
            
            fetch(`/api/v1/orders/{{ $order->id }}/items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Item removed successfully');
                    window.location.reload();
                } else {
                    toastr.error(data.error?.message || 'Failed to remove item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while removing the item');
            });
        }

        function handleToggleDiscount(event) {
            const discountId = event.detail;
            
            // Use existing Livewire component for discounts
            Livewire.emit('toggleDiscount', discountId);
        }

        function handleProcessPayment(event) {
            const orderId = event.detail;
            window.location.href = `/orders/${orderId}/payment`;
        }

        function handleCancelOrder(event) {
            const orderId = event.detail;
            
            fetch(`/api/v1/orders/${orderId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Order cancelled successfully');
                    window.location.href = "{{ route('orders.index') }}";
                } else {
                    toastr.error(data.error?.message || 'Failed to cancel order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while cancelling the order');
            });
        }

        function handlePrintOrder(event) {
            const orderId = event.detail;
            window.open(`/orders/${orderId}/print`, '_blank');
        }
    </script>
@endsection