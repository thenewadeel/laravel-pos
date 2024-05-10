<div class="card p-0 m-0">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        Payment
    </div>
    <div class="card-body">
        <div class="form-group">
            {{-- <label for="payments" class="font-weight-bold">Payments:</label> --}}
            {{-- <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Received By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->payments as $payment)
                        <tr>
                            <td>{{ $payment->created_at }}</td>
                            <td>{{ $payment->amount }}</td>
                            <td>{{ $payment->user->getFullName() }}</td>
                            <td>
                                <form method="post" action="{{ route('orders.payments.destroy', [$order, $payment]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table> --}}
        </div>
        <form id="payment-form" method="post" action="{{ route('orders.payments.store', $order) }}">
            <div class="form-inline justify-content-between">
                <label for="amount" class="text-lg">Amount:</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control mr-2 text-lg"
                    required value="{{ $order->balance() }}">
                <button form="payment-form" type="submit" class="btn btn-primary btn-block btn-sm my-2">Pay &
                    Close</button>
            </div>

            @csrf
        </form>
    </div>
</div>
