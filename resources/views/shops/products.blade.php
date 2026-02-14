@extends('layouts.admin')

@section('title')
    {{ __('Manage Shop Products') }} - {{ $shop->name }}
@endsection

@section('content-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">{{ __('Manage Products') }}</h1>
            <small class="text-muted">{{ $shop->name }}</small>
        </div>
        <a href="{{ route('shops.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ __('Back to Shops') }}
        </a>
    </div>
@endsection

@section('content')
    @include('layouts.partials.alert.success')
    @include('layouts.partials.alert.error')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Assign Products to Shop') }}</h3>
            <div class="card-tools">
                <span class="badge badge-info">
                    {{ count($assignedProducts) }} {{ __('products assigned') }}
                </span>
            </div>
        </div>
        <form action="{{ route('shops.products.update', $shop) }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    @foreach($products as $product)
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card h-100 {{ in_array($product->id, $assignedProducts) ? 'border-primary' : '' }}">
                                <div class="card-body p-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="product_{{ $product->id }}" 
                                               name="products[]" 
                                               value="{{ $product->id }}"
                                               {{ in_array($product->id, $assignedProducts) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="product_{{ $product->id }}">
                                            <div class="d-flex align-items-center">
                                                @if($product->image)
                                                    <img src="{{ Storage::url($product->image) }}" 
                                                         alt="{{ $product->name }}" 
                                                         class="img-thumbnail mr-2" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light mr-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-box text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $product->name }}</strong>
                                                    <div class="text-muted small">
                                                        {{ config('settings.currency_symbol') }}{{ number_format($product->price, 2) }}
                                                    </div>
                                                    @if(!$product->aval_status)
                                                        <span class="badge badge-danger badge-sm">{{ __('Inactive') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ __('Save Changes') }}
                </button>
                <a href="{{ route('shops.show', $shop) }}" class="btn btn-secondary">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection

@section('js')
<script>
    // Select all functionality
    document.addEventListener('DOMContentLoaded', function() {
        // You can add bulk selection functionality here if needed
    });
</script>
@endsection