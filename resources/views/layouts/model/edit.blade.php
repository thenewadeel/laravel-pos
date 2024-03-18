@extends('layouts.admin')


@section('title')
    Edit - @(yield 'title')
@endsection
@section('content-header')
    @(yield 'modelName')
@endsection
@section('content-actions')
    @yield('content-actions')
@endsection

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        <div class="card-body">
            <form action="@yield('route-update')" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @yield('form-fields')
                <button class="btn btn-primary" type="submit">{{ __('common.Update') }}</button>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            bsCustomFileInput.init();
        });
    </script>
@endsection
