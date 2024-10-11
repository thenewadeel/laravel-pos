<table class="table">
    <thead>
        <tr>
            <th>Order ID</th>
            <th>User ID</th>
            <th>Action Type</th>
            <th>Description</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orderHistories as $orderHistory)
        <tr>
            <td>{{ $orderHistory->order_id }}</td>
            <td>{{ $orderHistory->user_id }}</td>
            <td>{{ $orderHistory->action_type }}</td>
            <td>{{ $orderHistory->description }}</td>
            <td>{{ $orderHistory->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
