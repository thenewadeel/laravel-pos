@extends('layouts.model.show')

@section('title')
    {{ 'Shop Show' }}
@endsection
@section('content-header')
    {{ 'Shop:Show' }}
@endsection
@section('content-actions')
    {{-- {{ $order }} --}}
    <div class="mb-2">
        <a href="{{ route('shops.index') }}" class="btn btn-primary">{{ __('shop.Index') }}</a>

        {{-- <a href="{{ route('orders.print.preview', $order) }}" class="btn btn-primary ">
            {{ __('order.Print_Preview') }} <i class="fas fa-print"></i></a> --}}
    </div>
@endsection


@section('variables')
    @php($modelName = 'Shop')
    {{-- @php($modelObject = $order) --}}
@endsection

@section('content-details')
    <div class="container-fluid card p-2">
        <div class="card-header">
            Shop # {{ $shop }}
        </div>
        <div class="card-body">
            {{ $shop }}
            <hr>
            {{ $shop->categories }}


            <div class="card">
                <div class="card-body">
                    {{-- @include('layouts.partials.alert.error', ['errors' => $errors]) --}}
                    {{-- {{ $shops }} --}}

                    <h4>{{ __('shop.Categories') }}</h4>
                    <form action="{{ route('shop.updateCategories', $shop->id) }}" method="post">
                        @csrf
                        @method('post')
                        <div class="form-group">
                            @foreach ($categories as $category)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="category[]"
                                        value="{{ $category->id }}" id="category{{ $category->id }}"
                                        {{ $shop->categories->contains($category) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="category{{ $category->id }}">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-footer">
            Footer Content
        </div>
    </div>
@endsection

@section('footer-actions')
    <div class="d-flex justify-content-between w-100">

    </div>
@endsection
