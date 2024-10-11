<div class="mt-2 flex flex-row overflow-y-scroll bg-slate-200 rounded-md p-1 shadow-md hover:bg-blue-200 text-slate-700">
    {{-- <table class="table table-sm table-striped">
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
    </table> --}}
    {{-- <span class="font-bold">Description:</span> --}}
    {{-- <span class="text-gray-600 w-1/3">
    {{ $orderHistory->created_at ? $orderHistory->created_at->format('d-M-y H:i') : '' }}
    </span> --}}
    <div class="w-1/3">
        <i class="fas fa-circle-chevron-right text-sm mr-2"></i>
        {{ $orderHistory->action_type }}
    </div>

    <div class=" w-2/3">
        @if (Str::contains($orderHistory->action_type, 'pdf-generated'))
            <a class="text-blue-600 roundedn-md px-2 border-2 border-slate-300 shadow-md cursor-pointer hover:bg-blue-900 hover:text-white" href="{{ Storage::url($orderHistory->description) }}" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-download text-sm mr-2"></i>
                Download
            </a>
        @else
            {{ $orderHistory->description }}
        @endif
    </div>
</div>
