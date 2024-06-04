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


            <livewire:customer-select-create />
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
            <input type="hidden" name="customer_id" id="customer_id" value="{{ $order->customer_id ?? '' }}">
            <button type="submit" class="btn btn-primary col flex p-0 m-0">Update</button>
        </form>
    </div>

</div>
