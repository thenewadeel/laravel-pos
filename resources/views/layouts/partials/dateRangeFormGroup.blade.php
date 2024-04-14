@php
    $name = $name ?? 'dateRange';
    $startDate = $startDate ?? request('start_date', date('Y-m-d'));
    $endDate = $endDate ?? request('end_date', date('Y-m-d'));
    $stylingClasses = $stylingClasses ?? 'row';
@endphp
<div class="{{ $stylingClasses }}">
    <div class="form-group col p-0 m-0">
        <label for="start_date">{{ __('common.Start_Date') }}</label>
        <input type="date" name="start_date" id="{{ $name }}_start_date" class="form-control"
            value="{{ $startDate }}">
    </div>
    <div class="form-group col p-0 m-0">
        <label for="end_date">{{ __('common.End_Date') }}</label>
        <input type="date" name="end_date" id="{{ $name }}_end_date" class="form-control"
            value="{{ $endDate }}">
    </div>
</div>
