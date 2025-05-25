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
                    <a href="{{ route('shops.export') }}" class="btn btn-outline-info m-2 shadow-md">
                        <i class="fas fa-save fa-lg"></i>
                        Export Shops</a>
                    <a href="{{ route('customers.export') }}" class="btn btn-outline-info m-2 shadow-md">
                        <i class="fas fa-save fa-lg"></i>
                        Export Customers</a>
                </div>
            </div>
            {{-- TODO: Deletes --}}
            <div
                class=" flex flex-col md:flex-row items-center justify-start bg-white border-2 border-red-200 rounded-lg p-1 m-2">
                <div class="text-md font-bold text-gray-800 m-2">Deletes:</div>
                <div class="flex flex-col md:flex-row justify-evenly w-full">
                    <a href="" class="btn btn-outline-danger m-2 shadow-md">
                        <i class="fas fa-trash fa-lg"></i>
                        Clear Users</a>
                    <a href="{{ route('products.clear') }}" class="btn btn-outline-danger m-2 shadow-md"
                        onclick="return confirm('Are you sure you want to delete all products?')">
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
                    <form action="{{ route('shops.import') }}" method="post"
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
                    <form action="{{ route('customers.import') }}" method="post"
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
            {{-- TODO: DB Backup & Retore --}}
            {{-- <div
                class=" flex flex-col md:flex-row items-center justify-start bg-white border-2 border-slate-200 rounded-lg p-2 m-2">
                <div class="text-md font-bold text-gray-800 m-2">Database:</div>

                <div class="flex flex-col md:flex-row justify-start w-full">
                    <a href="" class="btn btn-success m-2 shadow-md">
                        <i class="fas fa-database fa-lg"></i>
                        Backup DB</a>
                </div>
            </div> --}}
        </div>
    @endif
    <div class="card">
        <div class="card-header text-lg font-bold">
            Preferences </div>
        <div class="card-body">
            <form action="{{ route('settings.store') }}" method="post">
                @csrf
                @foreach (App\Models\Setting::all() as $setting)
                    @if ($setting->key == 'club_logo')
                        <div class="flex flex-row border-2 border-gray-200 rounded-md justify-center items-center">
                            <label for="{{ $setting->key }}"
                                class="text-center w-1/4">{{ __('settings.' . $setting->key) }}</label>
                            <input type="text" name="{{ $setting->key }}"
                                class="form-control w-3/4 @error($setting->key) is-invalid @enderror"
                                id="{{ $setting->key }}" placeholder="{{ __('settings.' . $setting->key) }}"
                                value="{{ old($setting->key, $setting->value) }}">
                            @error($setting->key)
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @else
                        <div class="flex flex-row border-2 border-gray-200 rounded-md justify-center items-center">
                            <label for="{{ $setting->key }}"
                                class="text-center w-1/4">{{ __('settings.' . $setting->key) }}</label>
                            <input type="text" name="{{ $setting->key }}"
                                class="form-control w-3/4 @error($setting->key) is-invalid @enderror"
                                id="{{ $setting->key }}" placeholder="{{ __('settings.' . $setting->key) }}"
                                value="{{ old($setting->key, $setting->value) }}">
                            @error($setting->key)
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @endif
                @endforeach
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

        <div class="card">
            <div class="card-header text-lg font-bold">
                Jobs </div>
            <div class="card-body">
                @include('layouts.partials.orderExporterInterface', ['errors' => $errors])
                <div class="flex flex-col md:flex-row justify-evenly w-full">

                    @include('layouts.partials.userJobs', ['errors' => $errors])

                </div>
            </div>
        </div>
    @endif
@endsection
