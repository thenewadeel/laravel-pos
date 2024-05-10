<div class="card p-0 m-0">
    <div class="card-header p-1 m-0 text-lg font-bold text-center">
        Discounts
    </div>
    <div class="card-body p-1 m-0">
        {{-- {{ $order->discounts }} --}}
        <form method="POST" action="{{ route('orders.discounts.update', $order) }}">
            @csrf
            @method('PUT')
            <div class="flex justify-around">
                @foreach ($discounts as $discount)
                    <div class="form-check form-check-inline badge-info p-1 m-1 rounded-lg">
                        <input class="form-check-input" type="checkbox" name="discountsToAdd[]" value="{{ $discount->id }}"
                            {{ in_array($discount->id, $order->discounts->pluck('id')->toArray()) ? 'checked' : '' }}>
                        <label class="form-check-label" for="discount{{ $discount->id }}">
                            {{ $discount->name }} ({{ $discount->percentage }}%)
                        </label>
                    </div>
                @endforeach
            </div>


            <button type="submit" class="btn btn-primary col flex p-0 m-0">Update Discounts</button>
        </form>
    </div>
</div>
