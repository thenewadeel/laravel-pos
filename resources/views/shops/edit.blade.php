@extends('layouts.admin')

@section('title', __('shop.Edit_Shop'))
@section('content-header', __('shop.Edit_Shop'))

@section('content')
    @include('layouts.partials.alert.error', ['errors' => $errors])

    <div class="card">
        <div class="card-body">
            <form action="{{ route('shops.update', $shop) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
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
                    <label for="name">{{ __('shop.Name') }}</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        id="name" placeholder="{{ __('shop.Name') }}" value="{{ old('name', $shop->name) }}">
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="description">{{ __('shop.Description') }}</label>
                    <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                        id="description" placeholder="{{ __('shop.Description') }}"
                        value="{{ old('description', $shop->description) }}">
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="printer_ip">{{ __('shop.Printer_IP') }}</label>
                    <input type="text" name="printer_ip" class="form-control @error('printer_ip') is-invalid @enderror"
                        id="printer_ip" placeholder="{{ __('shop.Printer_IP') }}"
                        value="{{ old('printer_ip', $shop->printer_ip) }}">
                    @error('printer_ip')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    {{-- <label for="type">{{ __('shop.Type') }}</label>
                <textarea name="type" class="form-control @error('type') is-invalid @enderror"
                    id="type" placeholder="{{ __('shop.Type') }}">{{ old('type', $shop->type) }}</textarea> --}}
                    @include ('layouts.partials.selector', [
                        'name' => 'user_id',
                        'selected' => old('user_id', $shop->user_id),
                        'options' => \App\Models\User::pluck('email', 'id')->toArray(),
                        'label' => __('shop.User'),
                    ])
                    @error('type')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button class="btn btn-primary" type="submit">{{ __('common.Update') }}</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            {{-- @include('layouts.partials.alert.error', ['errors' => $errors]) --}}
            {{-- {{ $shops }} --}}

            <h4>{{ __('shop.Categories') }}</h4>
            <form action="{{ route('shop.updateCategories', $shop->id) }}" method="post">
                @csrf
                @method('post')
                <div class="form-group flex row ">
                    @foreach ($categories as $category)
                        <div class="m-2 p-2 form-check form-check-inline border border-gray-300 shadow-sm rounded">
                            <input class="form-check-input" type="checkbox" name="category[]" value="{{ $category->id }}"
                                id="category{{ $category->id }}"
                                {{ $shop->categories->contains($category) ? 'checked' : '' }}>
                            <label class="form-check-label"
                                for="category{{ $category->id }}">{{ $category->name }}</label>
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
