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

@php($orderItems = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))->orderBy('created_at', 'desc')->get())

@php($itemSales = \App\Models\OrderItem::whereIn('order_id', $orders->pluck('id'))->orderBy('created_at', 'asc')->get())
@php(
    $graphData = $itemSales->groupBy('product_name')->map(function ($gp) {
            return $gp->sum('price');
        })->sortByDesc(function ($value, $key) {
            return $value;
        })->take(15)
)

@section('content-details')
    <div class="container-fluid card p-2">
        <div class="card-header">
            Shop # {{ $shop->id }} - {{ $shop->name }}
        </div>
        <div class="card-body d-flex flex-column flex-wrap">
            {{-- {{ $shop }} --}}
            <hr class="w-100">
            <div class=" d-flex flex-row row flex-wrap shadow-sm  bg-light p-2 rounded-lg hover:shadow-xl">
                <h4 class="mt-2 mb-1">Categories Served:</h4>
                <div class="d-flex flex-wrap">
                    @foreach ($shop->categories as $category)
                        <div class="m-2">
                            <span class="btn btn-outline-primary btn-sm w-100">{{ $category->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <h4 class="mb-0">Total Orders: {{ $orders->count() }}</h4>
            <div class=" justify-content-between align-items-center flex flex-col md:flex-row w-full">
                <div style="width: 100%; height: auto;">
                    <canvas id="myChart"></canvas>
                </div>

                <div style="width: 100%;">
                    <canvas id="myChart2"></canvas>
                </div>
            </div>
        </div>

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

{{-- // $orders->groupBy(function ($order) {
                            //         return \Carbon\Carbon::parse($order->created_at)->format('Y-m-d');
                            //     })->map(function ($group) {
                            //         return $group->sum(function ($order) {
                            //             return $order->items->sum('price');
                            //         });
                            //     })->values()->toArray() --}}
{{-- {"id":350,"POS_number":"0021-03-04-2024","table_number":null,"waiter_name":null,"state":"preparing","type":"dine-in","customer_id":null,"user_id":1,"shop_id":12,"created_at":"2024-04-03T19:30:50.000000Z","updated_at":"2024-04-03T19:30:50.000000Z"} --}}



@section('js')
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"
        integrity="sha512-ZwR1/gSZM3ai6vCdI+LVF1zSq/5HznD3ZSTk7kajkaj4D292NLuduDCO1c/NT8Id+jE58KYLKT7hXnbtryGmMg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    <script src="{{ asset('js/libs/Chart.js/4.4.1/chart.umd.js') }}"></script>
    <script type="text/javascript">
        new Chart(
            document.getElementById('myChart2'), {
                type: 'bar',
                data: {
                    labels: @json($graphData->keys()),
                    datasets: [{
                        label: 'Sales by Product',
                        backgroundColor: 'rgb(220, 53, 69)',
                        borderColor: 'rgb(220, 53, 69)',

                        type: 'bar',

                        data: @json($graphData->values())
                    }]
                },
                options: {
                    indexAxis: 'y',

                    scales: {
                        x: {
                            type: 'linear',
                        },
                        y: {
                            type: 'category',
                        },
                        // y2: {
                        //     type: 'linear',
                        //     position: 'right',
                        //     grid: {
                        //         drawOnChartArea: false,
                        //     },
                        // }
                    }
                }
            }
        );
        new Chart(
            document.getElementById('myChart'), {
                type: 'line',
                data: {
                    @php(
    $LABELS = $itemSales->pluck('created_at')->map(function ($date) {
            $carbonDate = \Carbon\Carbon::parse($date);
            return $carbonDate->format('d M D');
        })->sort()->unique()->values()->toArray()
)
                    labels: @json($LABELS),
                    datasets: [{
                        label: 'Sales by Date',
                        backgroundColor: @json($LABELS).map(function(day) {
                            if (day.includes("Fri") || day.includes("Sat") || day.includes("Sun")) {
                                return 'rgba(220, 53, 69, 0.2)'
                            }
                            return 'rgba(20, 53, 69, 0.6)'
                        }),
                        borderColor: 'rgb(20, 53, 69)',
                        yAxisID: 'y2',
                        type: 'bar',
                        @php(
    $salesData = $orders->groupBy(function ($order) {
            return \Carbon\Carbon::parse($order->created_at)->format('d M D');
        })->map(function ($group) {
            return $group->sum(function ($order) {
                return $order->items->sum('price');
            });
        })->values()->toArray()
)
                        data: @json($salesData)
                    }, {
                        label: 'Orders',
                        backgroundColor: @json($LABELS).map(function(day) {
                            if (day.includes("Fri") || day.includes("Sat") || day.includes("Sun")) {
                                return 'rgb(220, 245, 153)'
                            }
                            return 'rgba(173, 216, 230, 0.5)'
                        }),
                        borderColor: 'rgb(40, 122, 198)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'y',
                        data: @json(
                            $orders->groupBy(function ($date) {
                                    return \Carbon\Carbon::parse($date->created_at)->format('M d');
                                })->map(function ($group) {
                                    return $group->count();
                                })->values()->toArray())
                    }]
                },
                options: {
                    scales: {
                        x: {
                            type: 'category',
                        },
                        y: {
                            type: 'linear',
                        },
                        y2: {
                            type: 'linear',
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            }
        );
    </script>
@endsection
