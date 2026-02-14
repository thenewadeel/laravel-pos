@extends('layouts.admin')

@section('title', __('user.Create_User'))
@section('content-header', __('user.Create_User'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        <div class="card-body">

            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="first_name">{{ __('user.FName') }}</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                        id="first_name" placeholder="{{ __('user.FName') }}" value="{{ old('first_name') }}">
                    @error('first_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="last_name">{{ __('user.LName') }}</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                        id="last_name" placeholder="{{ __('user.LName') }}" value="{{ old('last_name') }}">
                    @error('last_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">{{ 'Email' }}</label>
                    <input type="text" name="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" placeholder="{{ 'Email' }}" value="{{ old('email') }}">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                @include('layouts.partials.selector', [
                    'name' => 'type',
                    'selected' => old('type'),
                    'options' => [
                        'admin' => 'Admin',
                        'manager' => 'Manager',
                        'cashier' => 'Cashier',
                        'accountant' => 'Accountant',
                        'chef' => 'Chef',
                        'stockBoy' => 'Stock Boy',
                        'order-taker' => 'Order Taker',
                    ],
                    'label' => __('user.Type'),
                    'placeholder' => __('user.Type'),
                ])

                <div class="form-group">
                    <label for="password">{{ __('user.Password') }}</label>
                    <input name="password" type="password" class="form-control @error('password') is-invalid @enderror"
                        id="password" placeholder="{{ __('user.Password') }}">{{ old('password') }}
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">{{ __('user.password_confirmation') }}</label>
                    <input name="password_confirmation" type="password"
                        class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation"
                        placeholder="{{ __('user.password_confirmation') }}">{{ old('password') }}
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="image">{{ __('user.Image') }}</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="image" id="image">
                        <label class="custom-file-label" for="image">{{ __('user.Choose_file') }}</label>
                    </div>
                    @error('image')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
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
