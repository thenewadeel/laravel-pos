@extends('layouts.admin')

@section('title', __('customer.Update_Customer'))
@section('content-header', __('customer.Update_Customer'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        <div class="card-body">

            <form action="{{ route('customers.update', $customer) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">{{ __('Name') }}</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        id="name" placeholder="{{ __('Name') }}" value="{{ old('name', $customer->name) }}">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="membership_number">{{ 'Membership No' }}</label>
                    <input type="text" name="membership_number"
                        class="form-control @error('membership_number') is-invalid @enderror" id="membership_number"
                        placeholder="{{ 'Membership No' }}"
                        value="{{ old('membership_number', $customer->membership_number) }}">
                    @error('membership_number')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">{{ __('customer.Email') }}</label>
                    <input type="text" name="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" placeholder="{{ __('customer.Email') }}"
                        value="{{ old('email', $customer->email) }}">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">{{ __('customer.Phone') }}</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                        id="phone" placeholder="{{ __('customer.Phone') }}"
                        value="{{ old('phone', $customer->phone) }}">
                    @error('phone')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">{{ __('customer.Address') }}</label>
                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                        id="address" placeholder="{{ __('customer.Address') }}"
                        value="{{ old('address', $customer->address) }}">
                    @error('address')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="photo">{{ __('customer.photo') }}</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" name="photo" id="photo">
                        <label class="custom-file-label" for="photo">{{ __('customer.Choose_file') }}</label>
                    </div>
                    @error('photo')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>


                <button class="btn btn-primary" type="submit">Update</button>
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
