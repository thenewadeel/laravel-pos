<div>
    <table class="table table-responsive table-bordered table-striped table-sm">
        <thead>
            <tr>
                {{-- <th>{{ __('order.ID') }}</th> --}}
                <th class="col-1 align-middle">{{ __('discount.Name') }}</th>
                <th class="col-1 align-middle">{{ __('discount.Percentage') }}</th>
                <th class="col-1 align-middle">{{ __('discount.Amount') }}</th>
                <th class="col-1 align-middle">{{ __('discount.Method') }}</th>
                <th class="col-1 align-middle">{{ __('discount.Type') }}</th>
                <th class="col-1 align-middle">{{ __('discount.Actions') }}</th>

            </tr>
        </thead>
        <tbody>
            @foreach (App\Models\Discount::all() as $discount)
                <tr>
                    <form action="{{ route('discounts.update', $discount) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- <td>
                        <label for="name">Name:</label>
                    </td> --}}
                        <td>
                            <input class="form-control" type="text" id="name" name="name"
                                value="{{ $discount->name }}">
                        </td>
                        {{-- <label for="percentage">Percentage:</label> --}}
                        <td>
                            <input class="form-control" type="number" step="0.01" id="percentage" name="percentage"
                                value="{{ $discount->percentage }}">
                        </td>
                        {{-- <label for="amount">Amount:</label> --}}
                        <td>
                            <input class="form-control" type="number" step="0.01" id="amount" name="amount"
                                value="{{ $discount->amount }}">
                        </td>
                        {{-- <label for="method">Method:</label> --}}
                        <td>
                            <select class="form-control" id="method" name="method">
                                <option value="NATURAL" {{ $discount->method == 'NATURAL' ? 'selected' : '' }}>NATURAL
                                </option>
                                <option value="REVERSE" {{ $discount->method == 'REVERSE' ? 'selected' : '' }}>REVERSE
                                </option>
                            </select>
                        </td>
                        {{-- {{ $order->type == 'dine-in' ? 'selected' : '' }} --}}

                        {{-- <label for="type">Type:</label> --}}
                        <td>
                            <select class="form-control" id="type" name="type">
                                <option value="DISCOUNT" {{ $discount->type == 'DISCOUNT' ? 'selected' : '' }}>DISCOUNT
                                </option>
                                <option value="CHARGES" {{ $discount->type == 'CHARGES' ? 'selected' : '' }}>CHARGES
                                </option>
                            </select>
                        </td>
                        <td
                            class="flex justify-content-center align-middle h-full items-center border-2 border-green-700 p-0 m-0 ">
                            <button class="btn btn-primary h-full col-6 btn-sm px-0 mx-0" type="submit">Update</button>
                            {{-- </td> --}}
                            {{-- <td class="flex flex-row p-0 m-0 "> --}}
                            {{-- <form action="{{ route('discounts.destroy', $discount) }}" method="POST"> --}}
                            {{-- @csrf --}}
                            {{-- @method('DELETE') --}}
                            <a class="btn btn-danger col-4 btn-delete btn-sm px-0 mx-2"
                                data-url="{{ route('discounts.destroy', $discount) }}"><i class="fas fa-trash"></i></a>
                            {{-- </form> --}}
                        </td>
                    </form>
                </tr>
            @endforeach
        <tfoot>
            <tr>
                {{-- <td colspan="5"></td> --}}
                <td colspan="6">
                    <div class="flex justify-content-center">
                        <a class="btn btn-outline-primary btn-sm btn-block font-bold" type="button"
                            href="{{ route('discounts.create') }}">
                            <i class="fas fa-plus"></i>
                            Add New
                        </a>
                    </div>
                </td>
            </tr>
        </tfoot>
        </tbody>
    </table>
</div>



@section('js')
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script type="module">
        $(document).ready(function() {
            $(document).on('click', '.btn-delete', function() {
                var $this = $(this);
                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: 'btn btn-success',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                })

                swalWithBootstrapButtons.fire({
                    title: '{{ __('common.sure') }}',
                    text: '{{ __('common.really_delete') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('common.yes_delete') }}',
                    cancelButtonText: '{{ __('common.No') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.post($this.data('url'), {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        }, function(res) {
                            $this.closest('tr').fadeOut(500, function() {
                                $(this).remove();
                            })
                        })
                    }
                })
            })
        })
    </script>
@endsection
