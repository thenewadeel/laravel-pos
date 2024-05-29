@extends('layouts.admin')

@section('title', __('settings.Update_Settings'))
@section('content-header', __('settings.Update_Settings'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        <div class="card-body">
            <form action="{{ route('settings.store') }}" method="post">
                @csrf
                {{-- 
                <div class="flex flex-row border-2 border-gray-200 rounded-md justify-center items-center">
                    <label for="app_name" class="text-center w-1/4">{{ __('settings.App_name') }}</label>
                    <input type="text" name="app_name" class="form-control w-3/4 @error('app_name') is-invalid @enderror"
                        id="app_name" placeholder="{{ __('settings.App_name') }}"
                        value="{{ old('app_name', config('settings.app_name')) }}">
                    @error('app_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div> --}}

                {{-- <div class="flex flex-row border-2 border-gray-200 rounded-md justify-center items-center">
                    <label for="app_description">{{ __('settings.app_description') }}</label>
                    <textarea name="app_description" class="form-control w-3/4 @error('app_description') is-invalid @enderror"
                        id="app_description" placeholder="{{ __('settings.app_description') }}">{{ old('app_description', config('settings.app_description')) }}</textarea>
                    @error('app_description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div> --}}

                <div class="flex flex-row border-2 border-gray-200 rounded-md justify-center items-center">
                    <label for="currency_symbol" class="text-center w-1/4">{{ __('settings.Currency_symbol') }}</label>
                    <input type="text" name="currency_symbol"
                        class="form-control w-3/4 @error('currency_symbol') is-invalid @enderror" id="currency_symbol"
                        placeholder="{{ __('settings.Currency_symbol') }}"
                        value="{{ old('currency_symbol', config('settings.currency_symbol')) }}">
                    @error('currency_symbol')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                {{-- <div class="flex flex-row border-2 border-gray-200 rounded-md justify-center items-center">
                    <label for="warning_quantity">{{ __('settings.warning_quantity') }}</label>
                    <input type="text" name="warning_quantity"
                        class="form-control w-3/4 @error('warning_quantity') is-invalid @enderror" id="warning_quantity"
                        placeholder="{{ __('settings.warning_quantity') }}"
                        value="{{ old('warning_quantity', config('settings.warning_quantity')) }}">
                    @error('warning_quantity')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div> --}}
                <div class="flex flex-row border-2 border-gray-200 rounded-md justify-center items-center">
                    <label for="default_printer_ip"
                        class="text-center w-1/4">{{ __('settings.default_printer_ip') }}</label>
                    <input type="text" name="default_printer_ip"
                        class="form-control w-3/4 @error('default_printer_ip') is-invalid @enderror" id="default_printer_ip"
                        placeholder="{{ __('settings.default_printer_ip') }}"
                        value="{{ old('default_printer_ip', config('settings.default_printer_ip')) }}">
                    @error('default_printer_ip')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button type="submit"
                    class="btn btn-outline-primary btn-sm btn-block">{{ __('settings.Change_Setting') }}</button>
            </form>

        </div>
        @if (auth()->user()->type == 'admin')
            <div class="card">
                <div class="card-header text-lg font-bold">
                    {{ __('settings.Discounts') }}
                </div>
                <div class="card-body">
                    @include('layouts.partials.discountEdit')
                </div>
            </div>
        @endif
    </div>
@endsection
