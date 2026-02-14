# Offline Sync Setup Guide

## Overview

This guide will help you set up and configure the offline tablet synchronization feature for your POS system.

## Prerequisites

- Laravel 10.x or higher
- PHP 8.1 or higher
- MySQL 5.7+ or PostgreSQL 12+
- Redis (recommended for queue processing)

## Installation Steps

### 1. Run Migrations

The offline sync feature requires three new database tables. Run the migrations:

```bash
php artisan migrate
```

This will create:
- `order_sync_queues` - Queue management table
- `device_sync_logs` - Audit trail table
- Updates to `orders` table with sync fields

### 2. Publish Configuration (Optional)

The configuration file is already included at `config/offline-sync.php`. You can customize it by copying to your `.env` file:

```env
# Enable/disable offline sync
OFFLINE_SYNC_ENABLED=true

# Sync interval in minutes
OFFLINE_SYNC_INTERVAL=5

# Maximum orders per sync batch
OFFLINE_SYNC_BATCH_SIZE=10
OFFLINE_SYNC_MAX_ORDERS=100

# Conflict resolution strategy (manual, server_wins, client_wins, merge)
OFFLINE_SYNC_CONFLICT_RESOLUTION=manual

# Queue settings
OFFLINE_SYNC_QUEUE=default
OFFLINE_SYNC_QUEUE_PRIORITY=high

# Logging
OFFLINE_SYNC_LOG_LEVEL=info
OFFLINE_SYNC_LOG_RETENTION=30

# Notifications
OFFLINE_SYNC_NOTIFY_CONFLICT=true
OFFLINE_SYNC_NOTIFY_FAILURE=true
OFFLINE_SYNC_NOTIFICATION_EMAIL=admin@example.com
```

### 3. Setup Queue Worker

For production, you need to run a queue worker to process sync jobs:

```bash
# Using Laravel's queue worker
php artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90

# Or using Supervisor (recommended for production)
# See: https://laravel.com/docs/queues#supervisor-configuration
```

### 4. Configure Scheduled Tasks

Add the Laravel scheduler to your server's cron:

```bash
# Edit crontab
crontab -e

# Add this line (adjust path as needed)
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler is already configured in `app/Console/Kernel.php` to run sync every 5 minutes.

### 5. Seed Test Data (Development Only)

For development/testing, you can seed sample offline sync data:

```bash
php artisan db:seed --class=OfflineSyncSeeder
```

This creates:
- Pending orders for multiple devices
- Synced orders with history
- Orders with conflicts
- Failed orders for retry testing

### 6. Include CSS

Add the offline sync CSS to your layout. The file is at `public/css/offline-sync.css`.

For the admin layout, it's automatically available. For custom implementations:

```html
<link rel="stylesheet" href="{{ asset('css/offline-sync.css') }}">
```

## Usage

### For Waiters (Creating Orders)

1. **Navigate to Tablet Order page**: `/tablet-order`
2. **Enter order details**:
   - Table number
   - Waiter name (auto-filled)
   - Order type (dine-in, take-away, delivery)
   - Customer (optional)
3. **Add products**:
   - Use search box to find products
   - Click on product to add
   - Adjust quantities as needed
4. **Review order**: Check items and total
5. **Create order**: Click "Create Order" button

The order is now queued for sync and will be processed automatically.

### For Managers (Monitoring Sync)

1. **Navigate to Sync Status**: `/sync-status`
2. **View statistics**:
   - Total orders per device
   - Pending sync count
   - Synced orders
   - Failed syncs
   - Conflicts
3. **Filter by device**: Select specific tablet from dropdown
4. **Manual sync**: Click "Sync Device" or "Sync All Pending"
5. **Review failures**: Check failed syncs table and retry

### For Admins (Resolving Conflicts)

1. **Navigate to Conflict Resolution**: `/conflict-resolution`
2. **View conflicts**: See all orders with sync conflicts
3. **Resolve conflicts**:
   - Click "View" on a conflict
   - Review conflict details
   - Choose resolution strategy:
     - **Use Server**: Keep server version, discard local
     - **Update Server**: Overwrite server with local data
     - **Merge**: Combine both versions
     - **Adjust Quantity**: For inventory conflicts
   - Or click "Dismiss" to mark as failed

## Artisan Commands

### Manual Sync Operations

```bash
# Show current sync status
php artisan orders:sync-offline

# Sync all pending orders
php artisan orders:sync-offline --all

# Sync specific device
php artisan orders:sync-offline --device=tablet-floor-1

# Sync specific order
php artisan orders:sync-offline --order=123

# Preview without syncing (dry run)
php artisan orders:sync-offline --all --dry-run
```

### Scheduled Sync

```bash
# Run scheduled sync manually
php artisan orders:schedule-sync

# With options
php artisan orders:schedule-sync --interval=5 --max-orders=100 --notify
```

## Troubleshooting

### Orders Not Syncing

1. **Check queue worker**: Ensure `php artisan queue:work` is running
2. **Check logs**: Review `storage/logs/offline-sync.log`
3. **Verify order status**: Check if order has `sync_status = 'pending_sync'`
4. **Manual sync**: Try `php artisan orders:sync-offline --order=ID`

### Conflicts Not Resolving

1. **Check conflict type**: Different types require different resolution
2. **Review conflict data**: Click "View" to see details
3. **Check server order**: Verify the order exists on server
4. **Manual resolution**: Use conflict resolution UI

### High Failure Rate

1. **Check network**: Ensure tablets have intermittent connectivity
2. **Review retry count**: Orders with 5+ retries need attention
3. **Check product stock**: Inventory conflicts cause failures
4. **Monitor logs**: Look for error patterns

## Architecture

### Services

- **OfflineOrderService**: Creates and manages offline orders
- **ConflictResolutionService**: Detects and resolves conflicts
- **ProcessOfflineSyncQueue**: Background job for syncing

### Database Tables

- **orders**: Added `sync_status`, `local_order_id`, `device_id`, `synced_at`
- **order_sync_queues**: Queue management with retry logic
- **device_sync_logs**: Complete audit trail

### Flow

1. **Order Creation** → `OfflineOrderService::createOfflineOrder()`
2. **Queue Entry** → `OrderSyncQueue` record created
3. **Background Sync** → `ProcessOfflineSyncQueue` job dispatched
4. **Conflict Detection** → `ConflictResolutionService::detectConflict()`
5. **Resolution** → Manual or automatic based on strategy
6. **Audit** → `DeviceSyncLog` records all actions

## Security Considerations

1. **Authentication**: All routes require authentication
2. **Authorization**: Admin role required for conflict resolution
3. **Data Validation**: All inputs validated before processing
4. **Audit Trail**: Every action logged with device ID
5. **Transaction Safety**: Database operations use transactions

## Performance Optimization

1. **Queue Processing**: Use Redis for better queue performance
2. **Batch Size**: Adjust `OFFLINE_SYNC_BATCH_SIZE` based on load
3. **Indexing**: Database indexes on `sync_status`, `device_id`, `local_order_id`
4. **Cleanup**: Old logs purged automatically after retention period

## API Endpoints (Future)

While the current implementation uses services directly, these endpoints can be added for external integrations:

```
POST   /api/v1/sync/orders/upload    # Upload offline orders
GET    /api/v1/sync/orders/download  # Download pending orders
GET    /api/v1/sync/status           # Get sync status
POST   /api/v1/sync/acknowledge      # Acknowledge sync receipt
```

## Support

For issues or questions:
1. Check logs: `storage/logs/offline-sync.log`
2. Run diagnostics: `php artisan orders:sync-offline`
3. Review test suite: `php artisan test --filter=Offline`

## Updates

To update the offline sync feature in the future:

```bash
# Pull latest changes
git pull origin main

# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear
php artisan config:clear

# Restart queue worker
php artisan queue:restart
```
