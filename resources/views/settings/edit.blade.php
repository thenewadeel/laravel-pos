@extends('layouts.admin')

@section('title', __('settings.Update_Settings'))
@section('content-header', __('settings.Update_Settings'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])


    <livewire:user-profile />
    @if (auth()->user()->type == 'admin')
        <div class="card">
            <div class="card-header text-lg font-bold">
                Bulk Data Operations
            </div>
            {{-- <div class="card-body"> --}}

            <div
                class=" flex flex-col md:flex-row items-center justify-start bg-white border-2 border-slate-200 rounded-lg p-1 m-2">
                <div class="text-md font-bold text-gray-800 m-2">Exports:</div>
                <div class="flex flex-col md:flex-row justify-evenly w-full">
                    <a href="{{ route('users.export') }}" class="btn btn-outline-info m-2 shadow-md">
                        <i class="fas fa-save fa-lg"></i>
                        Export Users</a>
                    <a href="{{ route('products.export') }}" class="btn btn-outline-info m-2 shadow-md">
                        <i class="fas fa-save fa-lg"></i>
                        Export Products</a>
                    <a href="" class="btn btn-outline-info m-2 shadow-md">
                        <i class="fas fa-save fa-lg"></i>
                        Export Shops</a>
                    <a href="" class="btn btn-outline-info m-2 shadow-md">
                        <i class="fas fa-save fa-lg"></i>
                        Export Customers</a>
                </div>
            </div>
            <div
                class=" flex flex-col md:flex-row items-center justify-start bg-white border-2 border-red-200 rounded-lg p-1 m-2">
                <div class="text-md font-bold text-gray-800 m-2">Deletes:</div>
                <div class="flex flex-col md:flex-row justify-evenly w-full">
                    <a href="" class="btn btn-outline-danger m-2 shadow-md">
                        <i class="fas fa-trash fa-lg"></i>
                        Clear Users</a>
                    <a href="" class="btn btn-outline-danger m-2 shadow-md">
                        <i class="fas fa-trash fa-lg"></i>
                        Clear Products</a>
                    <a href="" class="btn btn-outline-danger m-2 shadow-md">
                        <i class="fas fa-trash fa-lg"></i>
                        Clear Shops</a>
                    <a href="" class="btn btn-outline-danger m-2 shadow-md">
                        <i class="fas fa-trash fa-lg"></i>
                        Clear Customers</a>
                </div>
            </div>

            <div
                class=" flex flex-col md:flex-row items-center justify-start bg-white border-2 border-slate-200 rounded-lg p-2 m-2">
                <div class="text-md font-bold text-gray-800 m-2">Imports:</div>

                <div class="flex flex-col md:flex-row justify-evenly w-full">

                    <form action="{{ route('users.import') }}" method="post" enctype="multipart/form-data"
                        class="flex flex-col items-center justify-center p-2  border-2 border-slate-200 rounded-md mx-1">
                        @csrf
                        <div class="mb-4">
                            <label for="xlsx_file" class="form-label">Upload a Users file</label>
                            <input type="file" name="xlsx_file" id="xlsx_file" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload fa-lg"></i>
                            Import Users</button>
                    </form>
                    <form action="{{ route('products.import') }}" method="post"
                        enctype="multipart/form-data"class="flex flex-col items-center justify-center p-2  border-2 border-slate-200 rounded-md mx-1">
                        @csrf
                        <div class="mb-4">
                            <label for="xlsx_file" class="form-label">Upload a Products file</label>
                            <input type="file" name="xlsx_file" id="xlsx_file" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary btn-blk">
                            <i class="fas fa-upload fa-lg"></i>
                            Import Products</button>
                    </form>
                    <form action="{{ route('products.import') }}" method="post"
                        enctype="multipart/form-data"class="flex flex-col items-center justify-center p-2  border-2 border-slate-200 rounded-md mx-1">
                        @csrf
                        <div class="mb-4">
                            <label for="xlsx_file" class="form-label">Upload a Shops file</label>
                            <input type="file" name="xlsx_file" id="xlsx_file" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary btn-blk">
                            <i class="fas fa-upload fa-lg"></i>
                            Import Shops</button>
                    </form>
                    <form action="{{ route('products.import') }}" method="post"
                        enctype="multipart/form-data"class="flex flex-col items-center justify-center p-2  border-2 border-slate-200 rounded-md mx-1">
                        @csrf
                        <div class="mb-4">
                            <label for="xlsx_file" class="form-label">Upload a Customers file</label>
                            <input type="file" name="xlsx_file" id="xlsx_file" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary btn-blk">
                            <i class="fas fa-upload fa-lg"></i>
                            Import Customers</button>
                    </form>
                </div>
            </div>
            <div
                class=" flex flex-col md:flex-row items-center justify-start bg-white border-2 border-slate-200 rounded-lg p-2 m-2">
                <div class="text-md font-bold text-gray-800 m-2">Database:</div>

                <div class="flex flex-col md:flex-row justify-start w-full">
                    <a href="" class="btn btn-success m-2 shadow-md">
                        <i class="fas fa-database fa-lg"></i>
                        Backup DB</a>
                </div>
            </div>
        </div>
    @endif
    <div class="card">
        <div class="card-header text-lg font-bold">
            Preferences </div>
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
                        class="form-control w-3/4 @error('default_printer_ip') is-invalid @enderror"
                        id="default_printer_ip" placeholder="{{ __('settings.default_printer_ip') }}"
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
@endsection
