@extends('layouts.model.create')

@section('title')
    {{ 'Category Create' }}
@endsection
@section('content-header')
    {{ 'Cat:Create' }}
@endsection
@section('content-actions')
    <button class="btn btn-primary">Test Button</button>
@endsection

@section('variables')
    @php($varName = 'test-variable')
    @php($varValue = 'test-value')
    @php($varData = ['test' => 'data'])
@endsection

@section('route-store', route('categories.store'))

@section('form-fields')

    <div class="form-group">
        <label for="name">{{ __('category.Name') }}</label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name"
            placeholder="{{ __('category.Name') }}" value="{{ old('name') }}">
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="description">{{ __('category.Description') }}</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="description"
            placeholder="{{ __('category.Description') }}">{{ old('description') }}</textarea>
        @error('description')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        <label for="image">{{ __('category.Image') }}</label>
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

@endsection
