@extends('layouts.admin')

@section('title', __('order.title'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div id="cart-tokens"></div>
    <!--cart></cart-->

@endsection
