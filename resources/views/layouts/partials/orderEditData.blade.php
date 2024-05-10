<div class="card p-0 m-0">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        Order Data
    </div>
    <div class="card-body p-1 m-0">

        <form action="{{ route('orders.update', ['order' => $order->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <!-- Form fields go here -->
            <div class="form-group col flex p-0 m-0">
                <label for="shop_id" class="col-md-4">Shop:</label>
                <select name="shop_id" id="shop_id" class="form-control">
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}" {{ $shop->id == $order->shop_id ? 'selected' : '' }}>
                            {{ $shop->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col flex p-0 m-0">

                <label for="type" class="col-md-4">Type:</label>
                <select name="type" id="type" class="form-control">
                    <option value="dine-in" {{ $order->type == 'dine-in' ? 'selected' : '' }}>Dine-in</option>
                    <option value="take-away" {{ $order->type == 'take-away' ? 'selected' : '' }}>Take Away</option>
                    <option value="delivery" {{ $order->type == 'delivery' ? 'selected' : '' }}>Delivery</option>
                </select>
            </div>
            <div class="form-group col flex p-0 m-0">

                <label for="waiter_name" class="col-md-4">Waiter:</label>
                <input type="text" name="waiter_name" id="waiter_name" class="form-control"
                    value="{{ old('waiter_name', $order->waiter_name) }}">
            </div>
            <div class="form-group col flex p-0 m-0">

                <label for="table_number" class="col-md-4">Table #:</label>
                <input type="number" name="table_number" id="table_number" class="form-control"
                    value="{{ old('table_number', $order->table_number) }}">
            </div>
            <div class="form-group col flex p-0 m-0">

                <label for="customer_id" class="col-md-4">Customer:</label>
                <select name="customer_id" id="customer_id" class="form-control">
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}"
                            {{ $customer->id == $order->customer_id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary col flex p-0 m-0">Update</button>
        </form>

    </div>
</div>
