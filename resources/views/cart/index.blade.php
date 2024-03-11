@extends('layouts.admin')

@section('title', __('order.title'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div id="cart"></div>
    <!--cart></cart-->

@endsection
