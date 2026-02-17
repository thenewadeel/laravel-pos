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
            <i class="fas fa-arrow-left"></i> {{ __('Back') }}
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
                <span class="badge badge-info" id="assigned-count">
                    {{ count($assignedProducts) }} {{ __('assigned') }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Filter -->
            <div class="form-group mb-4">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" class="form-control" id="product-search" 
                           placeholder="{{ __('Search products by name...') }}" autofocus>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="clear-search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <small class="text-muted mt-1 d-block">
                    <span id="showing-count">{{ count($products) }}</span> {{ __('products') }} | 
                    <span id="filtered-count">{{ count($products) }}</span> {{ __('showing') }}
                </small>
            </div>

            <form action="{{ route('shops.products.update', $shop) }}" method="POST" id="products-form">
                @csrf
                <div class="row" id="products-grid">
                    @foreach($products as $product)
                        <div class="col-md-3 col-sm-6 mb-3 product-item" data-name="{{ strtolower($product->name) }}">
                            <div class="card h-100 {{ in_array($product->id, $assignedProducts) ? 'border-primary' : '' }}">
                                <div class="card-body p-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input product-checkbox" 
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
                                                    <strong class="product-name">{{ $product->name }}</strong>
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

                <div class="card-footer bg-white border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('Save Changes') }}
                    </button>
                    <a href="{{ route('shops.show', $shop) }}" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('product-search');
        const clearBtn = document.getElementById('clear-search');
        const productItems = document.querySelectorAll('.product-item');
        const filteredCountEl = document.getElementById('filtered-count');
        const assignedCountEl = document.getElementById('assigned-count');

        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;

            productItems.forEach(item => {
                const productName = item.dataset.name;
                if (productName.includes(searchTerm)) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            filteredCountEl.textContent = visibleCount;
            
            // Show "no results" message if needed
            const noResultsEl = document.getElementById('no-results');
            if (visibleCount === 0 && searchTerm !== '') {
                if (!noResultsEl) {
                    const noResults = document.createElement('div');
                    noResults.id = 'no-results';
                    noResults.className = 'col-12 text-center py-5';
                    noResults.innerHTML = `
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('No products found') }}</h5>
                        <p class="text-muted">{{ __('Try a different search term') }}</p>
                    `;
                    document.getElementById('products-grid').appendChild(noResults);
                }
            } else if (noResultsEl) {
                noResultsEl.remove();
            }
        });

        // Clear search
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            productItems.forEach(item => item.style.display = '');
            filteredCountEl.textContent = productItems.length;
            const noResultsEl = document.getElementById('no-results');
            if (noResultsEl) noResultsEl.remove();
        });

        // Update assigned count on checkbox change
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const card = this.closest('.card');
                if (this.checked) {
                    card.classList.add('border-primary');
                } else {
                    card.classList.remove('border-primary');
                }
                
                const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
                assignedCountEl.textContent = checkedCount + ' assigned';
            });
        });

        // Keyboard shortcut: Ctrl/Cmd + F to focus search
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
            }
        });
    });
</script>
@endsection
