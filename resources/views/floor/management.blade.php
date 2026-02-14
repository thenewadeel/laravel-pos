@extends('layouts.admin')

@section('title')
    {{ __('Floor & Table Management') }}
@endsection

@section('content-header')
    <div class="flex flex-row justify-between items-center">
        <span class="text-lg font-semibold">
            <i class="fas fa-layer-group mr-2"></i> {{ __('Floor & Table Management') }}
        </span>
        <div class="flex gap-2">
            <a href="{{ route('floor.restaurant') }}" class="btn btn-info btn-sm">
                <i class="fas fa-utensils"></i> {{ __('Restaurant View') }}
            </a>
        </div>
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
        <!-- Floors List -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Floors') }}</h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ count($floors) }} {{ __('total') }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($floors as $floor)
                            <a href="{{ route('floor.management', ['floor' => $floor->id]) }}" 
                               class="list-group-item list-group-item-action {{ (request('floor') == $floor->id || (!request('floor') && $loop->first)) ? 'active' : '' }}">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $floor->name }}</h5>
                                    <small class="badge badge-{{ $floor->is_active ? 'success' : 'secondary' }}">
                                        {{ $floor->is_active ? __('Active') : __('Inactive') }}
                                    </small>
                                </div>
                                <p class="mb-1 text-muted">{{ $floor->description ?? __('No description') }}</p>
                                <small>
                                    <i class="fas fa-chair"></i> {{ $floor->tables_count ?? $floor->tables->count() }} {{ __('tables') }}
                                </small>
                            </a>
                        @empty
                            <div class="list-group-item text-center py-4">
                                <i class="fas fa-layer-group fa-2x text-muted mb-2"></i>
                                <p class="text-muted">{{ __('No floors created yet') }}</p>
                                @if(auth()->user()->type == 'admin')
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addFloorModal">
                                        <i class="fas fa-plus"></i> {{ __('Create First Floor') }}
                                    </button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Floor Stats -->
            @if(isset($currentFloor))
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Floor Statistics') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-success">{{ $currentFloor->tables->where('status', 'available')->count() }}</h4>
                            <small class="text-muted">{{ __('Available') }}</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-danger">{{ $currentFloor->tables->where('status', 'occupied')->count() }}</h4>
                            <small class="text-muted">{{ __('Occupied') }}</small>
                        </div>
                        <div class="col-6 mt-3">
                            <h4 class="text-warning">{{ $currentFloor->tables->where('status', 'reserved')->count() }}</h4>
                            <small class="text-muted">{{ __('Reserved') }}</small>
                        </div>
                        <div class="col-6 mt-3">
                            <h4 class="text-info">{{ $currentFloor->tables->where('status', 'cleaning')->count() }}</h4>
                            <small class="text-muted">{{ __('Cleaning') }}</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Tables Management -->
        <div class="col-md-8">
            @if(isset($currentFloor))
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            {{ $currentFloor->name }} - {{ __('Tables') }}
                        </h3>
                        <div>
                            @if(auth()->user()->type == 'admin')
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addTableModal">
                                    <i class="fas fa-plus"></i> {{ __('Add Table') }}
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editFloorModal">
                                    <i class="fas fa-edit"></i> {{ __('Edit Floor') }}
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($currentFloor->tables->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Table #') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Capacity') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($currentFloor->tables as $table)
                                            <tr>
                                                <td><strong>{{ $table->table_number }}</strong></td>
                                                <td>{{ $table->name ?? '-' }}</td>
                                                <td>
                                                    <i class="fas fa-users"></i> {{ $table->capacity }}
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ 
                                                        $table->status == 'available' ? 'success' : 
                                                        ($table->status == 'occupied' ? 'danger' : 
                                                        ($table->status == 'reserved' ? 'warning' : 
                                                        ($table->status == 'cleaning' ? 'info' : 'secondary')))
                                                    }}">
                                                        {{ ucfirst($table->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        @if(auth()->user()->type == 'admin')
                                                            <button type="button" class="btn btn-primary btn-edit-table" 
                                                                    data-table-id="{{ $table->id }}"
                                                                    data-table-number="{{ $table->table_number }}"
                                                                    data-table-name="{{ $table->name ?? '' }}"
                                                                    data-table-capacity="{{ $table->capacity }}"
                                                                    title="{{ __('Edit') }}">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-delete-table" 
                                                                    data-table-id="{{ $table->id }}"
                                                                    data-table-number="{{ $table->table_number }}"
                                                                    title="{{ __('Delete') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                        @if($table->status == 'available')
                                                            <a href="{{ route('orders.create', ['table' => $table->id]) }}" class="btn btn-success" title="{{ __('Create Order') }}">
                                                                <i class="fas fa-plus"></i>
                                                            </a>
                                                        @elseif($table->status == 'occupied')
                                                            @php $activeOrder = $table->getActiveOrder(); @endphp
                                                            @if($activeOrder)
                                                                <a href="{{ route('orders.edit', $activeOrder) }}" class="btn btn-info" title="{{ __('View Order') }}">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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

                <!-- Floor Layout Preview -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Floor Layout Preview') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="floor-layout-preview" style="min-height: 300px; background: #f8f9fa; border-radius: 8px; position: relative; padding: 20px;">
                            @if($currentFloor->tables->count() > 0)
                                @foreach($currentFloor->tables as $table)
                                    @php
                                        $leftPos = $table->position_x ?? ($loop->index * 90 + 20);
                                        $topPos = $table->position_y ?? 20;
                                        $statusClass = $table->status == 'available' ? 'bg-success' : ($table->status == 'occupied' ? 'bg-danger' : ($table->status == 'reserved' ? 'bg-warning' : ($table->status == 'cleaning' ? 'bg-info' : 'bg-secondary')));
                                    @endphp
                                    <div class="table-item" style="position: absolute; left: {{ $leftPos }}px; top: {{ $topPos }}px;">
                                        <div class="table-box text-center p-2 rounded {{ $statusClass }} text-white" style="width: 80px; height: 80px; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"
                                        title="Table {{ $table->table_number }} - {{ ucfirst($table->status) }}">
                                            <div class="font-weight-bold">{{ $table->table_number }}</div>
                                            <small><i class="fas fa-users"></i> {{ $table->capacity }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-th-large fa-2x mb-2"></i>
                                    <p>{{ __('No tables to display') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-arrow-left fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">{{ __('Select a floor to manage tables') }}</h4>
                        <p class="text-muted">{{ __('Click on a floor from the list on the left') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Floor Modal -->
    <div class="modal fade" id="addFloorModal" tabindex="-1" role="dialog" aria-labelledby="addFloorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('floor.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFloorModalLabel">{{ __('Add New Floor') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="floor_name">{{ __('Floor Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="floor_name" name="name" required placeholder="{{ __('e.g., Ground Floor') }}">
                        </div>
                        <div class="form-group">
                            <label for="floor_description">{{ __('Description') }}</label>
                            <textarea class="form-control" id="floor_description" name="description" rows="2" placeholder="{{ __('Optional description') }}"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="sort_order">{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" min="0">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="is_active">{{ __('Active') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Create Floor') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Floor Modal -->
    @if(isset($currentFloor) && auth()->user()->type == 'admin')
    <div class="modal fade" id="editFloorModal" tabindex="-1" role="dialog" aria-labelledby="editFloorModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('floor.update', $currentFloor) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFloorModalLabel">{{ __('Edit Floor') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_floor_name">{{ __('Floor Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_floor_name" name="name" value="{{ $currentFloor->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_floor_description">{{ __('Description') }}</label>
                            <textarea class="form-control" id="edit_floor_description" name="description" rows="2">{{ $currentFloor->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_sort_order">{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control" id="edit_sort_order" name="sort_order" value="{{ $currentFloor->sort_order }}" min="0">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1" {{ $currentFloor->is_active ? 'checked' : '' }}>
                                <label class="custom-control-label" for="edit_is_active">{{ __('Active') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-danger btn-delete-floor" data-floor-id="{{ $currentFloor->id }}" data-floor-name="{{ $currentFloor->name }}">{{ __('Delete Floor') }}</button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Update Floor') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Table Modal -->
    @if(isset($currentFloor) && auth()->user()->type == 'admin')
    <div class="modal fade" id="addTableModal" tabindex="-1" role="dialog" aria-labelledby="addTableModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('floor.table.store', $currentFloor) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTableModalLabel">{{ __('Add New Table') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="table_number">{{ __('Table Number') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="table_number" name="table_number" required placeholder="{{ __('e.g., T-001 or 12A') }}">
                        </div>
                        <div class="form-group">
                            <label for="table_name">{{ __('Table Name') }}</label>
                            <input type="text" class="form-control" id="table_name" name="name" placeholder="{{ __('e.g., Window Table') }}">
                        </div>
                        <div class="form-group">
                            <label for="capacity">{{ __('Capacity') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="4" required min="1" max="50">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Create Table') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('js')
<script>
    function deleteFloor(floorId, floorName) {
        if (confirm('{{ __("Are you sure you want to delete floor") }} "' + floorName + '"? {{ __("This will also delete all tables on this floor.") }}')) {
            // Create a form and submit it
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/api/v1/floors/' + floorId;
            
            var csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            var methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function editTable(tableId, tableNumber, tableName, capacity) {
        // Create a modal dynamically for editing
        var modalHtml = `
            <div class="modal fade" id="editTableModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="/api/v1/tables/${tableId}" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="PUT">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('Edit Table') }}</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>{{ __('Table Number') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="table_number" value="${tableNumber}" required>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Table Name') }}</label>
                                    <input type="text" class="form-control" name="name" value="${tableName || ''}">
                                </div>
                                <div class="form-group">
                                    <label>{{ __('Capacity') }} <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="capacity" value="${capacity}" required min="1" max="50">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ __('Update Table') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        var existingModal = document.getElementById('editTableModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body and show it
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        $('#editTableModal').modal('show');
    }

    function deleteTable(tableId, tableNumber) {
        if (confirm('{{ __("Are you sure you want to delete table") }} "' + tableNumber + '"?')) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/api/v1/tables/' + tableId;
            
            var csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            var methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Bind event listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Bind edit table buttons
        document.querySelectorAll('.btn-edit-table').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var tableId = this.dataset.tableId;
                var tableNumber = this.dataset.tableNumber;
                var tableName = this.dataset.tableName;
                var capacity = this.dataset.tableCapacity;
                editTable(tableId, tableNumber, tableName, capacity);
            });
        });

        // Bind delete table buttons
        document.querySelectorAll('.btn-delete-table').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var tableId = this.dataset.tableId;
                var tableNumber = this.dataset.tableNumber;
                deleteTable(tableId, tableNumber);
            });
        });

        // Bind delete floor button
        document.querySelectorAll('.btn-delete-floor').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var floorId = this.dataset.floorId;
                var floorName = this.dataset.floorName;
                deleteFloor(floorId, floorName);
            });
        });
    });
</script>
@endsection