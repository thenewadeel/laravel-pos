<div class="tablet-order-component">
    <div class="connection-status {{ $isOnline ? 'online' : 'offline' }}">
        {{ $isOnline ? 'Connected' : 'Offline' }}
    </div>

    @if($errorMessage)
        <div class="alert alert-danger">{{ $errorMessage }}</div>
    @endif

    @if($orderCreated)
        <div class="alert alert-success">Order created successfully!</div>
    @endif

    <div class="order-form">
        <div class="form-group">
            <label>Table Number</label>
            <input type="text" wire:model="tableNumber" class="form-control" placeholder="Enter table number">
            @error('tableNumber') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label>Waiter Name</label>
            <input type="text" wire:model="waiterName" class="form-control" placeholder="Enter waiter name">
        </div>

        <div class="form-group">
            <label>Order Type</label>
            <select wire:model="orderType" class="form-control">
                <option value="dine-in">Dine-in</option>
                <option value="take-away">Take-away</option>
                <option value="delivery">Delivery</option>
            </select>
        </div>

        <div class="form-group">
            <label>Customer</label>
            <select wire:model="customerId" class="form-control">
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="product-search">
        <input type="text" wire:model="productSearch" class="form-control" placeholder="Search products...">
    </div>

    <div class="products-grid">
        @foreach($products as $product)
            <div class="product-card" wire:click="addItem({{ $product->id }})">
                <h4>{{ $product->name }}</h4>
                <p>${{ number_format($product->price, 2) }}</p>
                <p>Stock: {{ $product->quantity }}</p>
            </div>
        @endforeach
    </div>

    <div class="order-items">
        <h3>Order Items</h3>
        @if(count($orderItems) === 0)
            <p>No items added</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderItems as $index => $item)
                        <tr>
                            <td>{{ $item['product_name'] }}</td>
                            <td>
                                <input type="number" 
                                       wire:change="updateQuantity({{ $index }}, $event.target.value)" 
                                       value="{{ $item['quantity'] }}" 
                                       min="1" 
                                       class="form-control" 
                                       style="width: 60px">
                            </td>
                            <td>${{ number_format($item['unit_price'], 2) }}</td>
                            <td>${{ number_format($item['total_price'], 2) }}</td>
                            <td>
                                <button wire:click="removeItem({{ $index }})" class="btn btn-danger btn-sm">×</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="order-total">
        <h3>Total: ${{ number_format($totalAmount, 2) }}</h3>
    </div>

    <div class="order-actions">
        <button wire:click="createOrder" class="btn btn-primary" {{ count($orderItems) === 0 ? 'disabled' : '' }}>
            Create Order
        </button>
        <button wire:click="clearOrder" class="btn btn-secondary">
            Clear
        </button>
    </div>

    <div class="local-order-id">
        <small>Order ID: {{ $localOrderId }}</small>
    </div>
</div>
