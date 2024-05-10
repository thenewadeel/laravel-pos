<div class="card p-0 m-0">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        Items
    </div>
    <div class="card-body p-1 m-0">
        <div class="form-group">
            {{-- <label for="items" class="font-weight-bold">Items:</label> --}}
            <table class="table table-striped table-sm p-0 m-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="">{{ $item->product->name }}</td>
                            <td class="text-right">{{ $item->product->price }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            {{-- <td>
                                <input type="number" name="items[{{ $item->id }}][quantity]"
                                    value="{{ $item->quantity }}" class="form-control">
                            </td> --}}
                            <td class="text-right">
                                {{ number_format($item->quantity * $item->product->price, 2) }}
                            </td>
                            <td>
                                <form action="{{ route('order.items.destroy', [$order, $item]) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <form action="{{ route('order.items.store', $order) }}" method="post"
                            class="d-flex align-items-center">
                            @csrf
                            <td colspan="2">
                                <select name="item" id="items-select" class="form-control me-2">
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}
                                            ({{ config('app.currency_symbol') }}
                                            {{ number_format($product->price, 2) }})
                                        </option>
                                    @endforeach
                                </select>

                            </td>
                            <td colspan="2">
                                <input type="number" name="quantity" id="quantity-input" class="form-control me-2"
                                    value="1" min="1">
                            </td>
                            <td>

                                <button type="submit" class="btn btn-primary">Add</button>
                            </td>
                        </form>



                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
