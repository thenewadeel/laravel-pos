<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Offline Sync Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for the offline tablet
    | synchronization feature. Adjust these settings based on your
    | restaurant's needs and infrastructure.
    |
    */

    'enabled' => env('OFFLINE_SYNC_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Sync Intervals
    |--------------------------------------------------------------------------
    |
    | Configure how frequently the system attempts to sync offline orders.
    | All values are in minutes.
    |
    */
    'sync_interval' => env('OFFLINE_SYNC_INTERVAL', 5),
    
    'retry_intervals' => [1, 5, 15, 30, 60], // Minutes between retries

    /*
    |--------------------------------------------------------------------------
    | Batch Processing
    |--------------------------------------------------------------------------
    |
    | Configure how many orders to process in a single batch.
    |
    */
    'batch_size' => env('OFFLINE_SYNC_BATCH_SIZE', 10),
    
    'max_orders_per_sync' => env('OFFLINE_SYNC_MAX_ORDERS', 100),

    /*
    |--------------------------------------------------------------------------
    | Conflict Resolution
    |--------------------------------------------------------------------------
    |
    | Default strategy for handling conflicts. Options: 'manual', 'server_wins',
    | 'client_wins', 'merge'
    |
    */
    'conflict_resolution' => env('OFFLINE_SYNC_CONFLICT_RESOLUTION', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Device Settings
    |--------------------------------------------------------------------------
    |
    | Configure device-specific settings.
    |
    */
    'device_timeout' => env('OFFLINE_SYNC_DEVICE_TIMEOUT', 30), // Seconds
    
    'max_devices_per_shop' => env('OFFLINE_SYNC_MAX_DEVICES', 10),

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Configure queue behavior for sync jobs.
    |
    */
    'queue' => env('OFFLINE_SYNC_QUEUE', 'default'),
    
    'queue_priority' => env('OFFLINE_SYNC_QUEUE_PRIORITY', 'high'),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for sync operations.
    |
    */
    'log_level' => env('OFFLINE_SYNC_LOG_LEVEL', 'info'),
    
    'log_retention_days' => env('OFFLINE_SYNC_LOG_RETENTION', 30),

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure notifications for sync events.
    |
    */
    'notify_on_conflict' => env('OFFLINE_SYNC_NOTIFY_CONFLICT', true),
    
    'notify_on_failure' => env('OFFLINE_SYNC_NOTIFY_FAILURE', true),
    
    'notification_email' => env('OFFLINE_SYNC_NOTIFICATION_EMAIL'),
];
