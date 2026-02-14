@extends('layouts.admin')

@section('title')
    {{ 'Floor & Restaurant Management' }}
@endsection

@section('content-header')
    <div class="flex flex-row justify-between items-center">
        <span class="text-lg font-semibold">
            <i class="fas fa-utensils mr-2"></i> Floor & Restaurant
        </span>
        <div class="flex gap-2">
            <span class="text-sm text-gray-600">
                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                <span class="badge badge-info">{{ auth()->user()->type }}</span>
            </span>
        </div>
    </div>
@endsection

@section('content-actions')
    <div class="flex flex-row justify-end space-x-2">
        <a href="{{ route('orders.index') }}" class="btn btn-dark btn-sm">
            <i class="nav-icon fas fa-list"></i>
            All Orders
        </a>
        <a href="{{ route('orders.workspace', $initialOrder->id ?? 1) }}" class="btn btn-info btn-sm">
            <i class="nav-icon fas fa-th-large"></i>
            Orders Workspace
        </a>
    </div>
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    {{-- Vue Floor Restaurant Component --}}
    <div id="floor-restaurant-app">
        <floor-restaurant-view
            :floors="{{ json_encode($floors) }}"
            :user="{{ json_encode(auth()->user()) }}"
            :daily-stats="{{ json_encode($dailyStats) }}"
        />
    </div>
@endsection

@section('js')
    @vite(['resources/js/floor-restaurant.js'])
@endsection