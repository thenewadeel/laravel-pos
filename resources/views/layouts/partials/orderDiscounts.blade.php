<div class="card p-0 m-0">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        Discounts
    </div>
    <div class="card-body p-1 m-0">
        {{-- {{ $order->discounts }} --}}
        <form method="POST" action="{{ route('orders.discounts.update', $order) }}">
            @csrf
            @method('PUT')
            <div class="flex flex-wrap">
                @foreach ($discounts as $discount)
                    <div
                        class=" p-0 py-1 m-0 rounded-lg col-4 flex flex-row align-middle items-center justify-start justify-items-center">
                        <div
                            class="form-check form-check-inline badge-{{ $discount->type == 'CHARGES' ? 'warning' : 'info' }} rounded-md p-0  m-0 ml-1">
                            <input class="form-check-input mx-1 p-0" type="checkbox" name="discountsToAdd[]"
                                value="{{ $discount->id }}"
                                {{ in_array($discount->id, $order->discounts->pluck('id')->toArray()) ? 'checked' : '' }}>
                            <label class="form-check-label m-0 p-0 text-md font-bold" for="discount{{ $discount->id }}">
                                {{ $discount->name }} ({{ $discount->percentage }}%)
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>


            <button type="submit" class="btn btn-primary col flex p-0 m-0">Update Discounts</button>
        </form>
    </div>
</div>
