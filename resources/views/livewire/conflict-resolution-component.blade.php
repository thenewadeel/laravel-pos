<div class="conflict-resolution-component">
    <div class="conflict-header">
        <h2>Conflict Resolution</h2>
        
        @if(session()->has('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        
        @if(session()->has('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        <div class="conflict-filters">
            <select wire:model="conflictTypeFilter" class="form-control">
                <option value="all">All Types</option>
                <option value="duplicate_order">Duplicate Order</option>
                <option value="insufficient_inventory">Insufficient Inventory</option>
                <option value="data_mismatch">Data Mismatch</option>
            </select>
            
            <input type="text" wire:model="searchQuery" class="form-control" placeholder="Search conflicts...">
            <button wire:click="clearFilters" class="btn btn-secondary">Clear</button>
        </div>
    </div>

    <div class="conflict-stats">
        <div class="stat-card">
            <h4>Total Conflicts</h4>
            <p>{{ $conflictStats['total'] }}</p>
        </div>
        @foreach($conflictStats['by_type'] as $type => $count)
            <div class="stat-card">
                <h4>{{ ucfirst(str_replace('_', ' ', $type)) }}</h4>
                <p>{{ $count }}</p>
            </div>
        @endforeach
    </div>

    <div class="conflicts-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Local ID</th>
                    <th>Device</th>
                    <th>Table</th>
                    <th>Amount</th>
                    <th>Conflict Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conflicts as $conflict)
                    <tr>
                        <td>{{ $conflict->local_order_id }}</td>
                        <td>{{ $conflict->device_id }}</td>
                        <td>{{ $conflict->order?->table_number ?? 'N/A' }}</td>
                        <td>${{ number_format($conflict->order?->total_amount ?? 0, 2) }}</td>
                        <td>
                            <span class="badge badge-warning">
                                {{ ucfirst(str_replace('_', ' ', $conflict->conflict_data['type'] ?? 'unknown')) }}
                            </span>
                        </td>
                        <td>
                            <button wire:click="viewConflictDetails({{ $conflict->id }})" class="btn btn-sm btn-info">
                                View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No conflicts found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        {{ $conflicts->links() }}
    </div>

    @if($showResolutionModal && $conflictDetails)
        <div class="modal show" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Resolve Conflict</h5>
                        <button wire:click="closeModal" class="close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="conflict-info">
                            <p><strong>Order ID:</strong> {{ $conflictDetails['sync_queue']['local_order_id'] ?? 'N/A' }}</p>
                            <p><strong>Device:</strong> {{ $conflictDetails['sync_queue']['device_id'] ?? 'N/A' }}</p>
                            <p><strong>Conflict Type:</strong> {{ ucfirst(str_replace('_', ' ', $conflictDetails['conflict_data']['type'] ?? 'unknown')) }}</p>
                            
                            @if(isset($conflictDetails['conflict_data']['message']))
                                <p><strong>Message:</strong> {{ $conflictDetails['conflict_data']['message'] }}</p>
                            @endif
                            
                            @if(isset($conflictDetails['conflict_data']['available_quantity']))
                                <p><strong>Available:</strong> {{ $conflictDetails['conflict_data']['available_quantity'] }}</p>
                                <p><strong>Requested:</strong> {{ $conflictDetails['conflict_data']['requested_quantity'] }}</p>
                            @endif
                        </div>

                        <div class="order-details">
                            <h6>Order Items</h6>
                            @if(isset($conflictDetails['order']['items']))
                                <ul>
                                    @foreach($conflictDetails['order']['items'] as $item)
                                        <li>{{ $item['product_name'] ?? 'Unknown' }} - Qty: {{ $item['quantity'] }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="resolution-options">
                            <h6>Resolution Options</h6>
                            
                            @if(($conflictDetails['conflict_data']['type'] ?? '') === 'duplicate_order')
                                <button wire:click="resolveConflict({{ $conflictDetails['sync_queue']['order_id'] }}, 'use_server')" class="btn btn-primary btn-block">
                                    Use Server Version
                                </button>
                                <button wire:click="resolveConflict({{ $conflictDetails['sync_queue']['order_id'] }}, 'update_server')" class="btn btn-secondary btn-block">
                                    Update Server with Local
                                </button>
                                <button wire:click="resolveConflict({{ $conflictDetails['sync_queue']['order_id'] }}, 'merge')" class="btn btn-info btn-block">
                                    Merge Orders
                                </button>
                            @elseif(($conflictDetails['conflict_data']['type'] ?? '') === 'insufficient_inventory')
                                <button wire:click="resolveInventoryConflict({{ $conflictDetails['sync_queue']['order_id'] }}, 'adjust_quantity')" class="btn btn-primary btn-block">
                                    Adjust to Available Quantity
                                </button>
                            @else
                                <button wire:click="resolveConflict({{ $conflictDetails['sync_queue']['order_id'] }}, 'use_server')" class="btn btn-primary btn-block">
                                    Use Server Version
                                </button>
                                <button wire:click="resolveConflict({{ $conflictDetails['sync_queue']['order_id'] }}, 'update_server')" class="btn btn-secondary btn-block">
                                    Update Server
                                </button>
                            @endif
                            
                            <button wire:click="dismissConflict({{ $conflictDetails['sync_queue']['order_id'] }})" class="btn btn-warning btn-block">
                                Dismiss (Mark as Failed)
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="closeModal" class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
