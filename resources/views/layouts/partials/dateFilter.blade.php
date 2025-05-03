@php
    $name = $name ?? 'dateRange';
    $startDate = $startDate ?? request('start_date', null);
    $endDate = $endDate ?? request('end_date', null);
    $stylingClasses =
        $stylingClasses ??
        'flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 lg:flex-row lg:space-x-4 lg:space-y-0';
@endphp
<div class="{{ $stylingClasses }}">
    <div class="flex items-center space-x-2">
        <label for="start_date" class="text-sm">{{ __('common.Start_Date') }}</label>
        <input type="date" name="start_date" id="{{ $name }}_start_date"
            class="border border-gray-300 rounded-md p-2 text-sm" value="{{ $startDate }}">
    </div>
    <div class="flex items-center space-x-2">
        <label for="end_date" class="text-sm">{{ __('common.End_Date') }}</label>
        <input type="date" name="end_date" id="{{ $name }}_end_date"
            class="border border-gray-300 rounded-md p-2 text-sm" value="{{ $endDate }}">
    </div>
</div>
