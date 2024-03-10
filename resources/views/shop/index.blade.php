@extends('layouts.shop')

@section('title', __('order.title'))

@section('content')
{{auth()->user()}}
    {{-- <div id="cart"></div> --}}
    <!--cart></cart-->

@endsection
