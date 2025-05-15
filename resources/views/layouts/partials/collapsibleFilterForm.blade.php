<?php
$mapToLabelValue = function ($model) {
    return ['label' => $model->name, 'value' => $model->id];
};
$mapToLabelValueUsers = function ($model) {
    return ['label' => $model->name(), 'value' => $model->id];
};

$selectionSets = [
    // [
    //     'name' => 'customer_ids',
    //     'label' => 'Customers',
    //     'items' => App\Models\Customer::select('id', 'name')->get()->map($mapToLabelValue)->toArray(),
    // ],
    [
        'name' => 'shop_ids',
        'label' => 'Shops',
        'items' => App\Models\Shop::select('id', 'name')->get()->map($mapToLabelValue)->toArray(),
    ],
    // [
    //     'name' => 'product_ids',
    //     'label' => 'Products',
    //     'items' => App\Models\Product::select('id', 'name')->get()->map($mapToLabelValue)->toArray(),
    // ],
    [
        'name' => 'cashiers',
        'label' => 'Cashiers',
        'items' => App\Models\User::where('type', 'cashier')->select('id', 'first_name', 'last_name')->get()->map($mapToLabelValueUsers)->toArray(),
    ],
    [
        'name' => 'order_takers',
        'label' => 'Order Takers',
        'items' => App\Models\User::where('type', 'order-taker')->select('id', 'first_name', 'last_name')->get()->map($mapToLabelValueUsers)->toArray(),
    ],
    [
        'name' => 'order_type',
        'label' => 'Order Type',
        'items' => [[['value' => 'dine-in', 'label' => 'Dine-In'], ['value' => 'take-away', 'label' => 'Take-Away'], ['value' => 'delivery', 'label' => 'Delivery']]],
    ],
];
?>

<div class="p-2 rounded-md border-2 border-blue-900 shadow-md shadow-blue-500  bg-neutral-100">
    <div x-data="{ expanded: false }">
        <button @click="expanded = ! expanded">
            <i class="ion ion-funnel text-3xl text-blue-500"></i>

        </button>

        <div x-show="expanded" x-collapse.duration.1000ms class="border-0 border-yellow-500  min-w-96">
            <form class="p-0 m-0 mb-4 md:px-4 max-w-screen-2xl" action="{{ route('home') }}" method="GET">
                <div
                    class=" flex flex-col md:flex-row w-full m-0 p-0 md:px-2 my-2 rounded-md  border border-slate-900 justify-start">
                    <div class="px-2 text-sm font-bold text-gray-700 ">
                        {{ __('common.Date_Filters') }}:
                    </div>

                    @include('layouts.partials.dateFilter', [
                        'name' => 'dateRange',
                        'start_date' => request('start_date', date('Y-m-d')),
                        'end_date' => request('end_date', date('Y-m-d')),
                        // 'stylingClasses' => 'col',
                    ])
                </div>


                <div
                    class=" flex flex-col md:flex-row justify-content-between  m-0 p-0 md:px-2 my-2 rounded-md  items-center self-center border border-slate-900">
                    <div class="px-2 text-sm font-bold text-gray-700 ">
                        {{ __('common.Select_Filters') }}:
                    </div>

                    @include('layouts.partials.dropdownMultiSelect', [
                        'selectionSets' => $selectionSets,
                    ])
                </div>
                {{-- @include('layouts.partials.filterModelSelect', [
                        'models' => App\Models\Customer::select('id', 'name')->get(),
                        'request_string' => 'customer_ids',
                        'modelName' => 'Customers',
                    ]) --}}

                {{-- @include('layouts.partials.filterModelSelect', [
                        'models' => App\Models\Shop::select('id', 'name')->get(),
                        'request_string' => 'shop_ids',
                        'modelName' => 'Shops',
                    ])
                    @include('layouts.partials.filterModelSelect', [
                        'models' => App\Models\Product::select('id', 'name')->get(),
                        'request_string' => 'item_ids',
                        'modelName' => 'Products',
                    ])
                    @include('layouts.partials.filterModelSelect', [
                        'models' => App\Models\User::where('type', 'cashier')->select('id', 'first_name', 'last_name')->get()->map(function ($user) {
                                return ['id' => $user->id, 'name' => $user->name()];
                            }),
                        'request_string' => 'cashiers',
                        'modelName' => 'Cashiers',
                    ])
                    @include('layouts.partials.filterModelSelect', [
                        'models' => App\Models\User::where('type', 'order-taker')->select('id', 'first_name', 'last_name')->get()->map(function ($user) {
                                return ['id' => $user->id, 'name' => $user->name()];
                            }),
                        'request_string' => 'order_takers',
                        'modelName' => 'Order Takers',
                    ])
                    @include('layouts.partials.filterModelSelectDropdown', [
                        'models' => [
                            ['id' => 'dine-in', 'name' => 'Dine-In'],
                            ['id' => 'take-away', 'name' => 'Take-Away'],
                            ['id' => 'delivery', 'name' => 'Delivery'],
                        ],
                        'request_string' => 'order_type',
                        'modelName' => 'Order Type',
                    ]) --}}
                <div class="flex space-x-2">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-4 w-2/3 rounded">{{ __('common.Filter') }}</button>
                    <a href="{{ route('home') }}"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-4 w-1/3 rounded text-center">{{ __('common.Clear') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
