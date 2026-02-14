<div class="sync-status-component">
    <div class="sync-header">
        <h2>Offline Sync Status</h2>
        
        <div class="sync-filters">
            <select wire:model="selectedDevice" class="form-control">
                <option value="">All Devices</option>
                @foreach($devices as $device)
                    <option value="{{ $device }}">{{ $device }}</option>
                @endforeach
            </select>
            
            <select wire:model="statusFilter" class="form-control">
                <option value="all">All Status</option>
                <option value="pending_sync">Pending</option>
                <option value="synced">Synced</option>
            </select>
            
            <input type="text" wire:model="searchQuery" class="form-control" placeholder="Search orders...">
            
            <button wire:click="clearFilters" class="btn btn-secondary">Clear</button>
            <button wire:click="refreshStatus" class="btn btn-info">Refresh</button>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="sync-stats">
        <div class="stat-card">
            <h4>Total Orders</h4>
            <p>{{ $deviceStats['total'] }}</p>
        </div>
        <div class="stat-card pending">
            <h4>Pending</h4>
            <p>{{ $deviceStats['pending'] }}</p>
        </div>
        <div class="stat-card synced">
            <h4>Synced</h4>
            <p>{{ $deviceStats['synced'] }}</p>
        </div>
        <div class="stat-card failed">
            <h4>Failed</h4>
            <p>{{ $deviceStats['failed'] }}</p>
        </div>
        <div class="stat-card conflicts">
            <h4>Conflicts</h4>
            <p>{{ $deviceStats['conflicts'] }}</p>
        </div>
    </div>

    <div class="sync-actions">
        @if($selectedDevice)
            <button wire:click="syncDevice('{{ $selectedDevice }}')" class="btn btn-primary">
                Sync Device: {{ $selectedDevice }}
            </button>
        @endif
        <button wire:click="syncAll" class="btn btn-primary">
            Sync All Pending
        </button>
        <span class="last-sync">Last Sync: {{ $lastSyncTime }}</span>
    </div>

    <div class="orders-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Local ID</th>
                    <th>Device</th>
                    <th>Table</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr class="status-{{ $order->sync_status }}">
                        <td>{{ $order->local_order_id }}</td>
                        <td>{{ $order->device_id }}</td>
                        <td>{{ $order->table_number }}</td>
                        <td>{{ $order->customer?->name ?? 'N/A' }}</td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $order->sync_status === 'synced' ? 'success' : 'warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $order->sync_status)) }}
                            </span>
                        </td>
                        <td>{{ $order->created_at?->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{ $orders->links() }}
    </div>

    @if($failedSyncs->count() > 0)
        <div class="failed-syncs">
            <h3>Failed Syncs</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Device</th>
                        <th>Error</th>
                        <th>Retries</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($failedSyncs as $sync)
                        <tr>
                            <td>{{ $sync->local_order_id }}</td>
                            <td>{{ $sync->device_id }}</td>
                            <td>{{ $sync->error_message }}</td>
                            <td>{{ $sync->retry_count }}</td>
                            <td>
                                <button wire:click="retryFailed({{ $sync->id }})" class="btn btn-sm btn-warning">
                                    Retry
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
