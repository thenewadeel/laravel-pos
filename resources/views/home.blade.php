@extends('layouts.admin')

@section('content-header')
    <div class="flex flex-col md:flex-row items-center">
        {{ __('dashboard.title') }}
        <div class="border-0 border-red-400 p-2 w-min ml-2 z-30">
            @include('layouts.partials.collapsibleFilterForm', ['errors' => $errors])
        </div>
    </div>
@endsection

<?php
// $orders = \App\Models\Order::all();
?>


@section('content')
    <script src="{{ asset('js/libs/Chart.js/4.4.1/chart.umd.js') }}"></script>
    {{-- Top Counters --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-4">
        @include('layouts.partials.dashboardCounter', [
            'title' => __('dashboard.Orders_Count'),
            'value' => $orders_count,
            'route' => 'orders.index',
        ])

        @include('layouts.partials.dashboardCounter', [
            'title' => __('dashboard.Customers_Count'),
            'value' => $customers_count,
            'route' => 'customers.index',
            'icon' => 'ion ion-man',
        ])

        @include('layouts.partials.dashboardCounter', [
            'title' => __('dashboard.Sales'),
            'value' => number_format($sales / 1000000, 1) . 'M',
            'icon' => 'ion ion-dollar',
        ])

        @include('layouts.partials.dashboardCounter', [
            'title' => __('dashboard.Income'),
            'value' => number_format($income / 1000000, 1) . 'M',
            'icon' => 'ion ion-dollar',
        ])

        @include('layouts.partials.dashboardCounter', [
            'title' => __('dashboard.Balance'),
            'value' => number_format($balance / 1000000, 1) . 'M',
            'icon' => 'ion ion-money',
        ])

        @include('layouts.partials.dashboardCounter', [
            'title' => __('dashboard.Average_Order_Value'),
            'value' => config('settings.currency_symbol') . number_format($average_order_value, 0),
            'icon' => 'ion ion-pie-graph',
        ])

        {{-- <div class="border border-dashed rounded-lg border-gray-300 dark:border-gray-600 h-32 flex-grow">
            <div class="small-box bg-warning">
                <div class="inner">
                    <div class="text-xl font-bold">{{ $customers_count }}</div>

                    <p>{{ __('dashboard.Customers_Count') }}</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="{{ route('customers.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div> --}}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        {{-- 1st line --}}
        <div class="border border-dashed rounded-lg border-gray-300 dark:border-gray-600 h-72">
            <div class='sales-chart-container shadow-md hover:shadow-none shadow-slate-400 transition-all  bg-neutral-100'>
                @include('layouts.partials.barChart', [
                    'dataSets' => [
                        [
                            'label' => 'Sale Trends',
                            'data' => $salesData,
                            'backgroundColor' =>
                                // ['red', 'green'],
                                collect($DateLabels)->map(function ($day) {
                                        if (str_contains($day, 'Sat') || str_contains($day, 'Sun')) {
                                            return 'rgba(0,255,0,0.35)';
                                        }
                                        return 'rgba(135, 206, 235, 0.75)'; // skyblue with 50% alpha
                                    })->toArray(),
                            // 'type' => 'bar',
                            'borderColor' => 'lightgreen',
                        ],
                    ],
                    'graphData' => collect($DateLabels)->mapWithKeys(fn($label) => [$label => 0]),
                    'chartId' => 'topSellingProductsChartaaa',
                    'chartTitle' => 'Sale Trends',
                    'indexAxis' => 'x',
                    'xAxisLabel' => '',
                    'yAxisLabel' => '',
                    'limit' => 365,
                ])
            </div>
        </div>
        {{-- 2nd line --}}
        <div
            class="border border-dashed rounded-lg border-gray-300 dark:border-gray-600 h-72 shadow-md hover:shadow-none shadow-slate-400 transition-all  bg-neutral-100">
            @include('layouts.partials.barChart', [
                'dataSets' => [
                    [
                        'label' => 'Most Valued',
                        'data' => $topValuedItems->values(),
                        'backgroundColor' => 'rgba(0,208,0,0.5)',
                        // 'type' => 'line',
                        'borderColor' => 'black',
                    ],
                ],
                'graphData' => $topValuedItems,
                'chartId' => 'topValuedItemsChart',
                'chartTitle' => 'Top Valued Items',
                'indexAxis' => 'x',
                'xAxisLabel' => '',
                'yAxisLabel' => '',
                // 'limit' => 5,
            ])
        </div>
    </div>
    {{-- <div class="border border-dashed rounded-lg border-gray-300 dark:border-gray-600 h-96 mb-4">


    </div> --}}

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
        <div class=" rounded-lg  h-72 sm:col-span-1 hover:shadow-inner shadow-slate-400 transition-all  bg-neutral-100">
            @include('layouts.partials.donutChart', [
                'dataSets' => [
                    [
                        'label' => 'Most Demanded',
                        'data' => $topSellingProducts->values(),
                        'backgroundColor' =>
                            // collect($DateLabels)->map(function ($day) {
                            //         if (str_contains($day, 'Sat') || str_contains($day, 'Sun')) {
                            //             return 'rgba(0,255,0,0.35)';
                            //         }
                            //         return 'rgba(135, 206, 235, 0.75)'; // skyblue with 50% alpha
                            //     })->toArray(),
                            collect([
                                'rgba(125, 185, 232, 0.5)',
                                'rgba(74, 144, 226, 0.5)',
                                'rgba(175, 141, 195, 0.5)',
                                'rgba(164, 203, 115, 0.5)',
                                'rgba(154, 102, 171, 0.5)',
                                'rgba(193, 125, 17, 0.5)',
                                'rgba(143, 143, 143, 0.5)',
                                'rgba(103, 118, 224, 0.5)',
                                'rgba(173, 203, 0, 0.5)',
                                'rgba(165, 82, 82, 0.5)',
                            ])->toArray(),
                        // 'type' => 'line',
                        // 'borderColor' => 'blue',
                    ],
                ],
                'graphData' => $topSellingProducts,
                'chartId' => 'topSellingProductsChart2',
                'chartTitle' => 'Most Demanded',
                'indexAxis' => 'y',
                'xAxisLabel' => '',
                'yAxisLabel' => '',
                // 'limit' => 5,
            ])
        </div>
        <div
            class="border border-dashed rounded-lg border-gray-300 dark:border-gray-600 h-72 sm:col-span-3 shadow-md hover:shadow-none shadow-slate-400 transition-all  bg-neutral-100">
            @include('layouts.partials.barChart', [
                'dataSets' => [
                    [
                        'label' => 'Top Selling Shops',
                        'data' => $topSellingShops->values(),
                        'backgroundColor' => 'rgba(100,149,237,0.5)',
                        // 'type' => 'line',
                        'borderColor' => 'black',
                    ],
                ],
                'graphData' => $topSellingShops,
                'chartId' => 'shopComparisonChart',
                'chartTitle' => 'Top Selling Shops',
                'indexAxis' => 'x',
                'xAxisLabel' => '',
                'yAxisLabel' => '',
                // 'limit' => 5,
            ])
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div
            class="border border-dashed rounded-lg border-gray-300 dark:border-gray-600 h-72 shadow-md hover:shadow-none shadow-slate-400 transition-all  bg-neutral-100">
            @include('layouts.partials.barChart', [
                'dataSets' => [
                    [
                        'label' => 'Most Orders Taken',
                        'data' => $mostBusyWaiters->values(),
                        'backgroundColor' => 'rgba(100,237,149,0.5)',
                        // 'type' => 'line',
                        'borderColor' => 'black',
                    ],
                ],
                'graphData' => $mostBusyWaiters,
                'chartId' => 'mostBusyWaitersChart',
                'chartTitle' => 'Top Waiters',
                'indexAxis' => 'x',
                'xAxisLabel' => '',
                'yAxisLabel' => '',
                // 'limit' => 5,
            ])

        </div>
        <div
            class="border border-dashed rounded-lg border-gray-300 dark:border-gray-600 h-72 shadow-md hover:shadow-none shadow-slate-400 transition-all  bg-neutral-100">
            @include('layouts.partials.barChart', [
                'dataSets' => [
                    [
                        'label' => 'Most Orders by Staff',
                        'data' => $mostOrdersTakenByUsers->values(),
                        'backgroundColor' => 'rgba(100,149,237,0.5)',
                        // 'type' => 'line',
                        'borderColor' => 'black',
                    ],
                ],
                'graphData' => $mostOrdersTakenByUsers,
                'chartId' => 'mostOrdersTakenByUsersChart',
                'chartTitle' => 'Top Staff',
                'indexAxis' => 'x',
                'xAxisLabel' => '',
                'yAxisLabel' => '',
                // 'limit' => 5,
            ])
        </div>

    </div>
    <div
        class="border border-dashed rounded-lg border-gray-300 dark:border-gray-600 h-72 mt-4 shadow-md hover:shadow-none shadow-slate-400 transition-all  bg-neutral-100">
        @include('layouts.partials.barChart', [
            'dataSets' => [
                [
                    'label' => 'Most Demanded',
                    'data' => $topSellingProducts->values(),
                    'backgroundColor' => 'rgba(135, 206, 235, 0.75)',
                    // 'type' => 'line',
                    'borderColor' => 'blue',
                ],
            ],
            'graphData' => $topSellingProducts,
            'chartId' => 'topSellingProductsChart',
            'chartTitle' => 'Most Demanded',
            'indexAxis' => 'y',
            'xAxisLabel' => '',
            'yAxisLabel' => '',
            // 'limit' => 5,
        ])
    </div>
@endsection
