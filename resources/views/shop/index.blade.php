@extends('layouts.admin')

@section('title', __('order.title'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    {{ auth()->user() }}
    {{-- <div id="cart"></div> --}}
    <!--cart></cart-->

@endsection
