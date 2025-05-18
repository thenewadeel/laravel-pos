@props([
    'target_route_name' => 'home',
    'target_route_args' => [],
])
<?php
$mapToLabelValue = function ($model) {
    return ['value' => $model->id, 'label' => $model->name];
};
$mapToLabelValueUsers = function ($model) {
    return ['value' => $model->id, 'label' => $model->name()];
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
        'items' => [['value' => 'dine-in', 'label' => 'Dine-In'], ['value' => 'take-away', 'label' => 'Take-Away'], ['value' => 'delivery', 'label' => 'Delivery']],
    ],
];
?>

<div class="p-2 rounded-md border-2 border-blue-900 shadow-md shadow-blue-500  bg-neutral-100">
    <div x-data="{ expanded: false }">
        <button @click="expanded = ! expanded">
            <i class="ion ion-funnel text-3xl text-blue-500"></i>
        </button>

        <div x-show="expanded" x-collapse.duration.1000ms class="border-0 border-yellow-500">
            <form class="p-0 m-0 md:px-4 max-w-screen-2xl" action="{{ route($target_route_name, $target_route_args) }}"
                method="GET">
                <div
                    class="flex flex-col md:flex-row w-full m-0 p-0 md:px-2 rounded-md border border-slate-400 justify-start bg-white">
                    <div class="px-4 py-2 text-sm font-bold text-gray-700 bg-neutral-200">
                        {{ __('common.Date_Filters') }}:
                    </div>
                    <div class="flex flex-row space-x-2 px-4 py-2">
                        @include('layouts.partials.dateFilter', [
                            'name' => 'dateRange',
                            'start_date' => request('start_date', date('Y-m-d')),
                            'end_date' => request('end_date', date('Y-m-d')),
                        ])
                    </div>
                </div>


                <div
                    class="flex flex-col md:flex-row w-full m-0 p-0 md:px-2 rounded-md border border-slate-400 justify-start bg-white mt-2">
                    <div class="px-4 py-2 text-sm font-bold text-gray-700 bg-neutral-200">
                        {{ __('common.Select_Filters') }}:
                    </div>
                    <div class="flex flex-row space-x-2 px-4 py-2 border-0 border-teal-300">
                        @include('layouts.partials.dropdownMultiSelect', [
                            'selectionSets' => $selectionSets,
                        ])
                    </div>
                </div>


                <div
                    class="flex flex-col md:flex-wrap w-full m-0 p-0 md:px-2 rounded-md border border-slate-400 justify-start bg-white mt-2">
                    <div class="px-4 py-2 text-sm font-bold text-gray-700 bg-neutral-200">
                        {{ __('common.Select_Filters2') }}:
                    </div>
                    <div class="flex flex-wrap space-x-2 px-4 py-2 border-0 border-teal-300">
                        <input type="search" name="pos_number" placeholder="{{ __('order.POS_Number') }}"
                            value="{{ request('pos_number') }}" id="posNumber" class="form-control p-0 m-0 w-auto">
                        <input type="search" name="customer_name" placeholder="{{ __('order.Customer_Name') }}"
                            value="{{ request('customer_name') }}" id="customerName"
                            class="form-control p-0 m-0 w-auto">
                        <input type="search" name="order_taker" placeholder="{{ __('order.Order_Taker') }}"
                            value="{{ request('order_taker') }}" id="orderTaker" class="form-control p-0 m-0 w-auto">
                        <input type="search" name="table_number" placeholder="{{ __('order.Table_Number') }}"
                            value="{{ request('table_number') }}" id="tableNumber" class="form-control p-0 m-0 w-auto">
                        <input type="search" name="waiter_name" placeholder="{{ __('order.Waiter_Name') }}"
                            value="{{ request('waiter_name') }}" style="width:200px" id="waiterName"
                            class="form-control p-0 m-0 w-auto">
                    </div>
                </div>

                <div class="flex flex-col space-2 px-2 mt-2 md:border-0 border-red-500 md:flex-row text-lg">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold my-1 mx-4 rounded-md md:w-full">{{ __('common.Filter') }}</button>
                    <a href="{{ route($target_route_name) }}"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold my-1 mx-4 rounded-md text-center md:w-full">{{ __('common.Clear') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
