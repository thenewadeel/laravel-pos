@extends('layouts.model.show')

@section('title')
    {{ 'Shop Show' }}
@endsection
@section('content-header')
    {{ 'Shop:Show' }}
@endsection
@section('content-actions')
    {{-- {{ $order }} --}}
    <div class="mb-2">
        <a href="{{ route('shops.index') }}" class="btn btn-primary">{{ __('shop.Index') }}</a>
        <a href="{{ route('shops.edit', $shop) }}" class="btn btn-primary">{{ __('shop.Edit') }}</a>

        {{-- <a href="{{ route('orders.print.preview', $order) }}" class="btn btn-primary ">
            {{ __('order.Print_Preview') }} <i class="fas fa-print"></i></a> --}}
    </div>
@endsection


@section('variables')
    @php($modelName = 'Shop')
    {{-- @php($modelObject = $order) --}}
@endsection

@section('content-details')
    <div class="container-fluid card p-2">
        <div class="card-header">
            Shop # {{ $shop->id }} - {{ $shop->name }}
        </div>
        <div class="card-body">
            {{-- {{ $shop }} --}}
            <hr>
            {{ $shop->categories }}
            <h4>Total Orders: {{ $orders->count() }}</h4>
            <div style="width: 600px; margin: auto;">
                <canvas id="myChart"></canvas>
            </div>
            < </div>
                {{-- <div class="card-footer"> --}}
                {{-- {{ $orders }} --}}
                <div class="accordion" id="accordionExamplex">
                    <div class="card">
                        <div class="card-header" id="headingOrders">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse"
                                    data-target="#collapseOrders" aria-expanded="true" aria-controls="collapseOrders">
                                    Orders
                                </button>
                            </h2>
                        </div>

                        <div id="collapseOrders" class="collapse show" aria-labelledby="headingOrders"
                            data-parent="#accordionExamplex">
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tr>
                                        <th>POS Number</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                    </tr>
                                    @foreach ($orders as $order)
                                        @foreach ($order->items as $product)
                                            <tr>
                                                <td>{{ $order->POS_number }}</td>
                                                <td>{{ $product->product->name }}</td>
                                                <td>{{ $product->quantity }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- </div> --}}
        </div>
    @endsection

    @section('footer-actions')
        <div class="d-flex justify-content-between w-100">
            <div class="d-flex justify-content-between w-100">
                @if ($previous)
                    <a href="{{ route('shops.show', ['shop' => $previous]) }}"
                        class="btn btn-primary {{ $previous == $shop->id ? 'disabled' : '' }}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif

                @if ($next)
                    <a href="{{ route('shops.show', ['shop' => $next]) }}"
                        class="btn btn-primary {{ $next == $shop->id ? 'disabled' : '' }}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @endif
            </div>
        </div>
    @endsection

    {{-- {"id":350,"POS_number":"0021-03-04-2024","table_number":null,"waiter_name":null,"state":"preparing","type":"dine-in","customer_id":null,"user_id":1,"shop_id":12,"created_at":"2024-04-03T19:30:50.000000Z","updated_at":"2024-04-03T19:30:50.000000Z"} --}}
    @section('js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"
            integrity="sha512-ZwR1/gSZM3ai6vCdI+LVF1zSq/5HznD3ZSTk7kajkaj4D292NLuduDCO1c/NT8Id+jE58KYLKT7hXnbtryGmMg=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript">
            const labels = @json(
                $orders->pluck('created_at')->map(function ($date) {
                        return \Carbon\Carbon::parse($date)->format('M d');
                    })->unique()->values()->toArray());

            const data = {
                labels: labels,
                datasets: [{
                    label: 'Orders per day',
                    backgroundColor: 'rgb(255, 99, 132)',
                    borderColor: 'rgb(255, 99, 132)',
                    data: @json(
                        $orders->groupBy(function ($date) {
                                return \Carbon\Carbon::parse($date->created_at)->format('M d');
                            })->map(function ($group) {
                                return $group->count();
                            })->values()->toArray())

                }]
            };

            const config = {
                type: 'line',
                data: data,
                options: {}
            };

            new Chart(
                document.getElementById('myChart'),
                config
            );
        </script>
    @endsection
