@extends('layouts.model.edit')

@section('title')
    {{ 'Category Edit' }}
@endsection
@section('content-header')
    {{ 'Cat:Edit' }}
@endsection
@section('content-actions')
    {{-- <button class="btn btn-primary">Test Button</button> --}}
@endsection

@section('variables')
    @php($varName = 'test-variable')
    @php($varValue = 'test-value')
    @php($varData = ['test' => 'data'])
@endsection

@section('route-update', route('categories.update', ['category' => $category->id]))

@section('form-fields')

    {{-- <div class="form-group">
        <label for="products">{{ __('category.Products') }}</label>
        <select name="products[]" class="select2 form-control @error('products') is-invalid @enderror" id="products" multiple>
            @foreach ($products as $product)
                <label for="name">{{ __('category.Name') }}</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name"
                    placeholder="{{ __('category.Name') }}" value="{{ $category->name }}">
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            @endforeach
    </div> --}}
    <div class="form-group">
        <label for="name">{{ __('category.Name') }}</label>
        <input name="name" class="form-control @error('name') is-invalid @enderror" id="name"
            placeholder="{{ __('category.Name') }}" value="{{ old('name', $category->name) }}">
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="description">{{ __('category.Description') }}</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="description"
            placeholder="{{ __('category.Description') }}">{{ $category->description }}</textarea>
        @error('description')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="image">{{ __('category.Image') }}</label>
        <img width="50" src="{{ $category->image }}" alt="">
        <div class="custom-file">
            <input type="file" class="custom-file-input" name="image" id="image">
            <label class="custom-file-label" for="image">{{ __('category.Choose_file') }}</label>
        </div>
        @error('image')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="kitchen_printer_ip">{{ __('category.Kitchen_printer_ip') }}</label>
        <input type="text" name="kitchen_printer_ip"
            class="form-control @error('kitchen_printer_ip') is-invalid @enderror" id="kitchen_printer_ip"
            placeholder="{{ __('product.Kitchen_printer_ip') }}" value="{{ old('kitchen_printer_ip', '192.168.0.165') }}">
        @error('kitchen_printer_ip')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
@endsection
