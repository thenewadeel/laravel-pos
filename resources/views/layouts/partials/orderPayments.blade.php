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
        {{-- <form id="payment-form" method="post" action="{{ route('orders.payments.store', $order) }}"> --}}
        <div class="form-inline justify-content-between">
            <label for="amount" class="text-lg">Amount:</label>
            <input type="number" step="0.01" name="amount" id="amount"
                class="form-control mr-2 text-lg display-none" required value="{{ $order->balance() }}">
            <a class="btn btn-primary btn-block btn-sm my-2 btn-pay">
                Pay & Close
            </a>
            {{-- <button class="btn btn-danger btn-delete" data-url="{{ route('shops.destroy', $shop) }}"><i
                        class="fas fa-trash"></i></button> --}}
        </div>

        @csrf
        {{-- </form> --}}
        {{-- {{ $order->customer }} --}}
    </div>
</div>

@section('js')
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script type="module">
        $(document).ready(function() {
            $(document).on('click', '.btn-pay', function() {
                var $this = $(this);
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })
                let options = {
                    title: '{{ __('order.Payment_Title') }}',
                    text: '{{ __('order.Payment_Text') }}',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('order.Yes_Pay') }}',
                    cancelButtonText: '{{ __('order.No_Pay') }}',
                    reverseButtons: true
                };
                let amount = document.getElementById('amount').value;
                let final_amount = {{ $order->balance() }};
                let url = "{{ route('orders.payments.store', $order) }}";
                console.log({
                    amount,
                    url
                })
                if (amount == {{ $order->balance() }}) {

                } else if (amount < {{ $order->balance() }}) { // Part-Chit
                    if ({{ $order->customer->id ?? 'null' }} != null) {
                        options.title = "<strong>" +
                            '{{ __('order.Part_Chit_Title') }}' +
                            " <u class='text-danger'>" +
                            (amount - {{ $order->balance() }}) +
                            "</u></strong>";
                        options.text = '{{ __('order.Part_Chit_Text') }}';
                        options.icon = 'warning';
                        final_amount = amount;
                    } else {
                        Swal.fire({
                            title: '{{ __('order.Part_Chit_Warning_Title') }}',
                            text: '{{ __('order.Part_Chit_Warning_Text') }}',
                            icon: "question"
                        });
                        return;
                    }
                } else {
                    options.title = "<strong>" +
                        '{{ __('order.Change_Title') }}' +
                        " <u class='text-success'>" +
                        (amount - {{ $order->balance() }}) +
                        "</u></strong>";
                    //  '{{ __('order.Change_Title') }}';
                    options.text = '{{ __('order.Change_Text') }}';
                    options.icon = 'success';
                }
                swalWithBootstrapButtons.fire(options).then((result) => {
                    if (result.value) {
                        $.post(url, {
                            _method: 'POST',
                            _token: '{{ csrf_token() }}',
                            amount: final_amount
                        }).done(function() {
                            window.location.href = "{{ route('orders.show', $order) }}";
                        });
                    }
                })

            })
        })
    </script>
@endsection
