<div class="card p-0 m-0">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        Order Data
    </div>
    <div class="card-body p-1 m-0">
        @php($shops = auth()->user() ? auth()->user()->shops : $shops)
        <form action="{{ route('makeNeworder') }}" method="POST">
            @csrf
            {{-- @method('PUT') --}}
            <!-- Form fields go here -->
            <div class="form-group col flex p-0 m-0">
                <label for="shop_id" class="col-md-4">Shop:</label>
                <select name="shop_id" id="shop_id" class="form-control">
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}">
                            {{ $shop->name }}
                        </option>
                    @endforeach
                </select>
                @error('shop_id')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-group col flex p-0 m-0">

                <label for="type" class="col-md-4">Type:</label>
                <select name="type" id="type" class="form-control" onchange="handleTypeChange(event)">
                    <option value="dine-in">Dine-in</option>
                    <option value="take-away">Take Away</option>
                    <option value="delivery">Delivery</option>
                </select>
                @error('type')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group col flex p-0 m-0" id="waiter_name-div">

                <label for="waiter_name" class="col-md-4">Waiter:</label>
                <input type="text" name="waiter_name" id="waiter_name" class="form-control"
                    value="{{ old('waiter_name') }}">
                @error('waiter_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-group col flex p-0 m-0" id="table_number-div">

                <label for="table_number" class="col-md-4">Table #:</label>
                <input type="number" name="table_number" id="table_number" class="form-control"
                    value="{{ old('table_number') }}">
                @error('table_number')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group flex flex-col p-0 m-0">
                <div class="flex flex-row p-0 m-0">
                    <label for="searchCustomer" class="col-md-4 p-0 m-0">Customer:</label>
                    <input id="searchCustomer" name="searchCustomer" type="text" placeholder="Search customer..."
                        class="form-control ">
                    {{-- <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
            data-target="#exampleModal">
            +
        </button> --}}
                </div>
                <div class="border-2 border-green-700 col-12 p-0 m-0 mt-1 w-full bg-white rounded-md shadow-lg">
                    <div id="customerDropdown" class="border-4 border-green-300 " style="display: none;">
                        <ul
                            class="py-1 max-h-32 rounded-md text-base leading-6 shadow-xs overflow-auto focus:outline-none sm:text-sm sm:leading-5 ring-1 ring-black ring-opacity-5">
                            @foreach (App\Models\Customer::all() as $customer)
                                <li id="customer_id_{{ $customer->id }}"
                                    class=" py-2 pl-3 pr-9 text-gray-900 cursor-pointer hover:bg-indigo-500 hover:text-white">
                                    <div class="flex items-center justify-between cursor-pointer"
                                        onclick="
                                            document.getElementById('customer_id').value = {{ $customer->id }};
                                            document.getElementById('searchCustomer').value = '{{ $customer->name }}';
                                            document.getElementById('customerDropdown').style.display = 'none';
                                            ">
                                        <span class="font-normal text-indigo-600" id="customer_id_{{ $customer->id }}"
                                            data-id="{{ $customer->id }}">{{ $customer->membership_number }}</span>
                                        <span>{{ $customer->name }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <script>
                    (function() {
                        // document.getElementById('customerDropdown').addEventListener('click', function(e) {
                        //     console.log(e.target);
                        //     if (e.target.tagName === 'LI') {
                        //         var customerId = e.target.querySelector('.font-normal').getAttribute('data-id');
                        //         // console.log(customerId);
                        //         document.getElementById('customer_id').value = customerId;
                        //         document.getElementById('searchCustomer').value = e.target.querySelector('span:last-child')
                        //             .textContent;
                        //         document.getElementById('customerDropdown').style.display = 'none';
                        //     }
                        // });


                        // document.addEventListener('click', function(e) {
                        //     var dropdown = document.getElementById('customerDropdown');
                        //     if (!dropdown.contains(e.target) && e.target.id !== 'searchCustomer') {
                        //         dropdown.style.display = 'none';
                        //     }
                        // });
                    })();

                    document.getElementById('searchCustomer').addEventListener('input', function() {
                        var input = this.value.toLowerCase();
                        var dropdown = document.getElementById('customerDropdown');
                        var items = dropdown.getElementsByTagName('li');

                        Array.prototype.forEach.call(items, function(item) {
                            var text = item.textContent.toLowerCase();
                            var displayStyle = text.includes(input) ? 'block' : 'none';
                            item.style.display = displayStyle;
                        });

                        dropdown.style.display = 'block';
                    });
                </script>
            </div>

            <script>
                function handleTypeChange(event) {
                    switch (event.target.value) {
                        case 'dine-in':
                            document.getElementById('table_number-div').style.display = 'flex';
                            id = "table_number"
                            document.getElementById('waiter_name-div').style.display = 'flex';

                            break;
                        default:
                            document.getElementById('table_number-div').style.display = 'none';
                            document.getElementById('waiter_name-div').style.display = 'none';
                            document.getElementById('table_number').value = null;
                            document.getElementById('waiter_name').value = null;
                            break;
                    }
                }
            </script>
            <div class="form-group col flex p-0 m-0">
                <label for="notes" class="col-md-4">Notes:</label>
                <input type="text" name="notes" id="notes" class="form-control" value="{{ old('notes') }}">
            </div>
            <input type="hidden" name="customer_id" id="customer_id" {{-- value="{{ $order->customer_id ?? '' }}" --}}>
            <button type="submit" class="btn btn-primary col flex p-0 m-0">Start Order</button>
        </form>
    </div>

</div>
