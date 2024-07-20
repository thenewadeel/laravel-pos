@extends('layouts.admin')

@section('title', __('user.Edit_User'))
@section('content-header', __('user.Edit_User'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card d-flex flex row flex-row">
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
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
                <div class="form-group">
                    <label for="first_name">{{ __('user.FName') }}</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                        id="first_name" placeholder="{{ __('user.FName') }}"
                        value="{{ old('first_name', $user->first_name) }}">
                    @error('first_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="last_name">{{ __('user.LName') }}</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                        id="last_name" placeholder="{{ __('user.LName') }}"
                        value="{{ old('last_name', $user->last_name) }}">
                    @error('last_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">{{ 'Email' }}</label>
                    <input type="text" name="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" placeholder="{{ 'Email' }}" value="{{ old('email', $user->email) }}">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    {{-- <label for="type">{{ __('user.Type') }}</label>
                <textarea name="type" class="form-control @error('type') is-invalid @enderror"
                    id="type" placeholder="{{ __('user.Type') }}">{{ old('type', $user->type) }}</textarea> --}}
                    @include ('layouts.partials.selector', [
                        'name' => 'type',
                        'selected' => old('type', $user->type),
                        'options' => [
                            'admin' => 'admin',
                            'cashier' => 'cashier',
                            'accountant' => 'accountant',
                            'order-taker' => 'order-taker',
                        ],
                    ])
                    @error('type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>



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


                <button class="btn btn-primary" type="submit">{{ __('common.Update') }}</button>
            </form>
        </div>
        {{-- </div>

    <div class="card"> --}}
        <div class="card-body">
            {{-- @include('layouts.partials.alert.error', ['errors' => $errors]) --}}
            {{-- {{ $shops }} --}}

            <h4>{{ __('user.Shops') }}</h4>
            <form action="{{ route('users.updateShops', $user->id) }}" method="post">
                @csrf
                @method('post')
                <div class="form-group">
                    @foreach ($shops as $shop)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="shops[]" value="{{ $shop->id }}"
                                id="shop{{ $shop->id }}" {{ $user->shops->contains($shop) ? 'checked' : '' }}>
                            <label class="form-check-label" for="shop{{ $shop->id }}">
                                {{ $shop->name }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary">{{ __('common.Update') }}</button>
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
