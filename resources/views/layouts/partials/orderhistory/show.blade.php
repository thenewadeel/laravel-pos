<div class="mt-2">
    <table class="table table-sm table-striped">
        <tbody>
            <tr>
                <th>Order</th>
                <th>User</th>
                <th>Action</th>
                <th>Time</th>
            </tr>
            <tr>
                <td><a
                        href="{{ route('orders.show', ['order' => $orderHistory->order_id]) }}">{{ $orderHistory->order->POS_number }}</a>
                </td>
                <td>{{ $orderHistory->user->getFullname() }}</td>
                <td>{{ $orderHistory->action_type }}</td>
                <td>{{ $orderHistory->created_at ? $orderHistory->created_at->format('d-M-y H:i') : '' }}</td>
            </tr>
        </tbody>
    </table>
    {{-- <span class="font-bold">Description:</span> --}}
    <span class="text-gray-600">{{ $orderHistory->description }}</span>
</div>
