@extends('layouts.admin')

@section('title')
    {{ __('Floors & Tables') }}
@endsection

@section('content-header')
    <div class="flex flex-row justify-between items-center">
        <span class="text-lg font-semibold">
            <i class="fas fa-th-large mr-2"></i> {{ __('Floors & Tables') }}
        </span>
    </div>
@endsection

@section('content-actions')
    @if(auth()->user()->type == 'admin')
    <div class="flex flex-row justify-end space-x-2">
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addFloorModal">
            <i class="fas fa-plus"></i> {{ __('Add Floor') }}
        </button>
    </div>
    @endif
@endsection

@section('content')
    @include('layouts.partials.alert.success')
    @include('layouts.partials.alert.error')

    <div class="row">
        <!-- Floors Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Floors') }}</h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($floors as $floor)
                            <a href="{{ route('floor.management', ['floor' => $floor->id]) }}" 
                               class="list-group-item list-group-item-action {{ (request('floor') == $floor->id || (!request('floor') && $loop->first)) ? 'active' : '' }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $floor->name }}</h5>
                                    <span class="badge badge-{{ $floor->is_active ? 'success' : 'secondary' }}">
                                        {{ $floor->tables->count() }} tables
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="list-group-item text-center py-4">
                                <p class="text-muted">{{ __('No floors created') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables List -->
        <div class="col-md-9">
            @if(isset($currentFloor))
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            {{ $currentFloor->name }} 
                            <small class="text-muted">({{ $currentFloor->tables->count() }} tables)</small>
                        </h3>
                        @if(auth()->user()->type == 'admin')
                            <div>
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addTableModal">
                                    <i class="fas fa-plus"></i> {{ __('Add Table') }}
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editFloorModal">
                                    <i class="fas fa-edit"></i> {{ __('Edit') }}
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-0">
                        @if($currentFloor->tables->count() > 0)
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('#') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Capacity') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currentFloor->tables as $table)
                                        <tr>
                                            <td><strong>{{ $table->table_number }}</strong></td>
                                            <td>{{ $table->name ?: '-' }}</td>
                                            <td>{{ $table->capacity }} seats</td>
                                            <td>
                                                @php
                                                    $statusClass = match($table->status) {
                                                        'available' => 'success',
                                                        'occupied' => 'danger',
                                                        'reserved' => 'warning',
                                                        'cleaning' => 'info',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge badge-{{ $statusClass }}">
                                                    {{ ucfirst($table->status) }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                @if($table->status == 'available')
                                                    <form action="{{ route('makeNeworder') }}" method="POST" class="d-inline start-order-form">
                                                        @csrf
                                                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                                                        <input type="hidden" name="shop_id" value="{{ $shopId ?? 1 }}">
                                                        <input type="hidden" name="type" value="dine-in">
                                                        <input type="hidden" name="customer_id" value="1">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="fas fa-plus"></i> {{ __('Start Order') }}
                                                        </button>
                                                    </form>
                                                @elseif($table->status == 'occupied')
                                                    @php $activeOrder = $table->getActiveOrder(); @endphp
                                                    @if($activeOrder)
                                                        <a href="{{ route('orders.workspace.view', $activeOrder) }}" class="btn btn-info btn-sm">
                                                            <i class="fas fa-edit"></i> {{ __('Continue') }}
                                                        </a>
                                                    @endif
                                                @endif
                                                @if(auth()->user()->type == 'admin')
                                                    <button type="button" class="btn btn-primary btn-sm btn-edit-table" 
                                                            data-table-id="{{ $table->id }}"
                                                            data-table-number="{{ $table->table_number }}"
                                                            data-table-name="{{ $table->name ?? '' }}"
                                                            data-table-capacity="{{ $table->capacity }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete-table" 
                                                            data-table-id="{{ $table->id }}"
                                                            data-table-number="{{ $table->table_number }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-chair fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">{{ __('No tables on this floor') }}</h5>
                                @if(auth()->user()->type == 'admin')
                                    <button type="button" class="btn btn-primary mt-2" data-toggle="modal" data-target="#addTableModal">
                                        <i class="fas fa-plus"></i> {{ __('Add First Table') }}
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-arrow-left fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">{{ __('Select a floor to manage tables') }}</h4>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Floor Modal -->
    <div class="modal fade" id="addFloorModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('floor.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add Floor') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('Floor Name') }} *</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g., Ground Floor">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control" name="sort_order" value="0" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Floor Modal -->
    @if(isset($currentFloor) && auth()->user()->type == 'admin')
    <div class="modal fade" id="editFloorModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('floor.update', $currentFloor) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Edit Floor') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('Floor Name') }} *</label>
                            <input type="text" class="form-control" name="name" value="{{ $currentFloor->name }}" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control" name="sort_order" value="{{ $currentFloor->sort_order }}" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-delete-floor" data-floor-id="{{ $currentFloor->id }}">{{ __('Delete') }}</button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Table Modal -->
    @if(isset($currentFloor) && auth()->user()->type == 'admin')
    <div class="modal fade" id="addTableModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('floor.table.store', $currentFloor) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add Table') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('Table Number') }} *</label>
                            <input type="text" class="form-control" name="table_number" required placeholder="e.g., T-01">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Table Name') }}</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g., Window Table">
                        </div>
                        <div class="form-group">
                            <label>{{ __('Capacity') }} *</label>
                            <input type="number" class="form-control" name="capacity" value="4" required min="1" max="50">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('js')
<script>
    // Edit Table
    document.querySelectorAll('.btn-edit-table').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tableId = this.dataset.tableId;
            const tableNumber = this.dataset.tableNumber;
            const tableName = this.dataset.tableName;
            const capacity = this.dataset.tableCapacity;
            
            const modalHtml = `
                <div class="modal fade" id="editTableModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="/tables/${tableId}" method="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="PUT">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('Edit Table') }}</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Table Number *</label>
                                        <input type="text" class="form-control" name="table_number" value="${tableNumber}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Table Name</label>
                                        <input type="text" class="form-control" name="name" value="${tableName || ''}">
                                    </div>
                                    <div class="form-group">
                                        <label>Capacity *</label>
                                        <input type="number" class="form-control" name="capacity" value="${capacity}" required min="1" max="50">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('editTableModal')?.remove();
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            $('#editTableModal').modal('show');
        });
    });

    // Delete Table
    document.querySelectorAll('.btn-delete-table').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tableId = this.dataset.tableId;
            const tableNumber = this.dataset.tableNumber;
            
            if (confirm(`{{ __('Delete table') }} "${tableNumber}"?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/tables/' + tableId;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Delete Floor
    document.querySelectorAll('.btn-delete-floor').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const floorId = this.dataset.floorId;
            
            if (confirm(`{{ __('Delete this floor and all its tables?') }}`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/floors/' + floorId;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
</script>
@endsection
