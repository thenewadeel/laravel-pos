@extends('layouts.admin')

@section('title', __('shop.Create_Shop'))
@section('content-header', __('shop.Create_Shop'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        <div class="card-body">

            <form action="{{ route('shops.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="first_name">{{ __('shop.Name') }}</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        id="name" placeholder="{{ __('shop.Name') }}" value="{{ old('name') }}">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="description">{{ __('shop.Description') }}</label>
                    <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                        id="description" placeholder="{{ __('shop.Description') }}" value="{{ old('description') }}">
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="image">{{ __('shop.Image') }}</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="image" id="image">
                        <label class="custom-file-label" for="image">{{ __('shop.Choose_file') }}</label>
                    </div>
                    @error('image')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    {{-- <label for="user_id">{{ __('shop.user_idfg') }}</label> --}}
                    {{-- <input type="text" name="user_id" class="form-control @error('user_id') is-invalid @enderror"
                        id="user_id" placeholder="{{ __('shop.user_id') }}" value="{{ old('user_id') }}"> --}}
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    @include ('layouts.partials.selector', [
                        'name' => 'user_id',
                        'options' => \App\Models\User::pluck('email', 'id')->toArray(),
                    ])
                </div>

                <button class="btn btn-primary" type="submit">{{ __('common.Create') }}</button>
            </form>
        </div>
    </div>
@endsection

@section('js')
    {{-- <script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            bsCustomFileInput.init();
        });
    </script> --}}
@endsection
