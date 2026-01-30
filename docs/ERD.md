# Entity Relationship Diagram (ERD) - Laravel POS System

## Current Database Schema

### Core Entities

#### 1. Users
```sql
users
├── id (PK, BIGINT, Auto Increment)
├── first_name (VARCHAR)
├── last_name (VARCHAR)
├── email (VARCHAR, Unique)
├── email_verified_at (TIMESTAMP, Nullable)
├── password (VARCHAR)
├── remember_token (VARCHAR, Nullable)
├── current_shop_id (BIGINT, FK to shops.id, Nullable)
├── created_at (TIMESTAMP)
├── updated_at (TIMESTAMP)
└── favorite_printer_ip (VARCHAR, Nullable)
```

#### 2. Shops
```sql
shops
├── id (PK, BIGINT, Auto Increment)
├── name (VARCHAR)
├── address (TEXT, Nullable)
├── phone (VARCHAR, Nullable)
├── printer_ip (VARCHAR, Nullable)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 3. Categories
```sql
categories
├── id (PK, BIGINT, Auto Increment)
├── name (VARCHAR)
├── parent_id (BIGINT, FK to categories.id, Nullable)
├── created_at (TIMESTAMP)
├── updated_at (TIMESTAMP)
└── lft (INT, Nullable) -- For nested set model
├── rgt (INT, Nullable) -- For nested set model
```

#### 4. Products
```sql
products
├── id (PK, BIGINT, Auto Increment)
├── name (VARCHAR)
├── description (TEXT, Nullable)
├── image (VARCHAR, Nullable)
├── price (DECIMAL 8,2)
├── quantity (INT, Default 1000)
├── status (BOOLEAN, Default true)
├── kitchen_printer_ip (VARCHAR, Nullable)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 5. Customers
```sql
customers
├── id (PK, BIGINT, Auto Increment)
├── name (VARCHAR)
├── membership_number (VARCHAR, Unique)
├── email (VARCHAR, Nullable)
├── phone (VARCHAR, Nullable)
├── address (TEXT, Nullable)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 6. Orders
```sql
orders
├── id (PK, BIGINT, Auto Increment)
├── POS_number (VARCHAR, Unique)
├── table_number (VARCHAR, Nullable)
├── waiter_name (VARCHAR, Nullable)
├── state (ENUM: 'preparing', 'served', 'closed', 'wastage')
├── type (ENUM: 'dine-in', 'take-away', 'delivery')
├── customer_id (BIGINT, FK to customers.id, Nullable)
├── user_id (BIGINT, FK to users.id)
├── shop_id (BIGINT, FK to shops.id)
├── subtotal (DECIMAL 10,2)
├── discount_amount (DECIMAL 10,2, Default 0.00)
├── tax_amount (DECIMAL 10,2, Default 0.00)
├── total_amount (DECIMAL 10,2)
├── notes (TEXT, Nullable)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 7. Order Items
```sql
order_items
├── id (PK, BIGINT, Auto Increment)
├── order_id (BIGINT, FK to orders.id)
├── product_id (BIGINT, FK to products.id)
├── quantity (INT)
├── unit_price (DECIMAL 8,2)
├── total_price (DECIMAL 10,2)
├── notes (TEXT, Nullable)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 8. Discounts
```sql
discounts
├── id (PK, BIGINT, Auto Increment)
├── name (VARCHAR)
├── percentage (DECIMAL 5,2)
├── start_date (DATE, Nullable)
├── end_date (DATE, Nullable)
├── is_active (BOOLEAN, Default true)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 9. Payments
```sql
payments
├── id (PK, BIGINT, Auto Increment)
├── order_id (BIGINT, FK to orders.id)
├── amount (DECIMAL 10,2)
├── type (ENUM: 'cash', 'card', 'digital_wallet', 'other')
├── transaction_id (VARCHAR, Nullable)
├── notes (TEXT, Nullable)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

### Pivot Tables

#### 10. Category Products
```sql
category_product (Many-to-Many)
├── category_id (BIGINT, FK to categories.id)
├── product_id (BIGINT, FK to products.id)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 11. Discount Orders
```sql
discount_order (Many-to-Many)
├── discount_id (BIGINT, FK to discounts.id)
├── order_id (BIGINT, FK to orders.id)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 12. Discount Products
```sql
discount_product (Many-to-Many)
├── discount_id (BIGINT, FK to discounts.id)
├── product_id (BIGINT, FK to products.id)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 13. Shop Users
```sql
shop_user (Many-to-Many)
├── shop_id (BIGINT, FK to shops.id)
├── user_id (BIGINT, FK to users.id)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 14. Shop Products
```sql
shop_product (Many-to-Many)
├── shop_id (BIGINT, FK to shops.id)
├── product_id (BIGINT, FK to products.id)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

### Activity Logging Tables (Spatie Package)

#### 15. Activity Log
```sql
activity_log
├── id (PK, BIGINT, Auto Increment)
├── log_name (VARCHAR)
├── description (TEXT)
├── subject_type (VARCHAR, Nullable)
├── subject_id (BIGINT, Nullable)
├── causer_type (VARCHAR, Nullable)
├── causer_id (BIGINT, Nullable)
├── properties (JSON, Nullable)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

### Queue Tables

#### 16. Jobs
```sql
jobs
├── id (PK, BIGINT, Auto Increment)
├── queue (VARCHAR)
├── payload (JSON)
├── attempts (TINYINT)
├── reserved_at (INT, Nullable)
├── available_at (INT)
├── created_at (INT)
```

#### 17. Failed Jobs
```sql
failed_jobs
├── id (PK, BIGINT, Auto Increment)
├── uuid (VARCHAR, Unique)
├── connection (TEXT)
├── queue (TEXT)
├── payload (JSON)
├── exception (TEXT)
├── failed_at (TIMESTAMP)
```

## Relationship Diagram

```
Users ────< Orders >─── Customers
 │         │           │
 │         │           └─── Payments
 │         │
 │         ├─── Order_Items ──── Products
 │         │                    │
 │         │                    ├─── Categories (M-N)
 │         │                    │
 │         │                    └── Discounts (M-N)
 │         │
 │         └─── Discounts (M-N)
 │
 └─── Shop_User (M-N) ──── Shops
                           │
                           └─── Shop_Product (M-N) ──── Products
```

## 2026 Database Enhancements

### New Tables for Offline Functionality

#### 18. Tablet Devices
```sql
tablet_devices
├── id (PK, BIGINT, Auto Increment)
├── device_id (VARCHAR, Unique)
├── name (VARCHAR)
├── user_id (BIGINT, FK to users.id)
├── shop_id (BIGINT, FK to shops.id)
├── last_sync_at (TIMESTAMP, Nullable)
├── is_active (BOOLEAN, Default true)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 19. Offline Orders
```sql
offline_orders
├── id (PK, BIGINT, Auto Increment)
├── local_order_id (VARCHAR) -- Local identifier on tablet
├── tablet_device_id (BIGINT, FK to tablet_devices.id)
├── order_data (JSON) -- Complete order structure
├── sync_status (ENUM: 'pending', 'synced', 'conflict', 'failed')
├── conflict_data (JSON, Nullable)
├── server_order_id (BIGINT, Nullable, FK to orders.id)
├── created_at (TIMESTAMP)
├── updated_at (TIMESTAMP)
└── synced_at (TIMESTAMP, Nullable)
```

#### 20. Sync Logs
```sql
sync_logs
├── id (PK, BIGINT, Auto Increment)
├── tablet_device_id (BIGINT, FK to tablet_devices.id)
├── sync_type (ENUM: 'full', 'incremental', 'conflict_resolution')
├── records_synced (INT, Default 0)
├── records_failed (INT, Default 0)
├── sync_duration_ms (INT)
├── error_details (JSON, Nullable)
├── created_at (TIMESTAMP)
```

#### 21. Product Snapshots
```sql
product_snapshots
├── id (PK, BIGINT, Auto Increment)
├── tablet_device_id (BIGINT, FK to tablet_devices.id)
├── product_id (BIGINT, FK to products.id)
├── product_data (JSON) -- Product state at time of sync
├── version (INT) -- For conflict resolution
├── created_at (TIMESTAMP)
└── expires_at (TIMESTAMP) -- Cache expiration
```

### Enhanced Tables for 2026 Features

#### 22. Tables (Enhanced)
```sql
tables
├── id (PK, BIGINT, Auto Increment)
├── table_number (VARCHAR, Unique)
├── shop_id (BIGINT, FK to shops.id)
├── capacity (INT)
├── location (VARCHAR, Nullable) -- Section/area in restaurant
├── qr_code (VARCHAR, Nullable) -- For customer ordering
├── status (ENUM: 'available', 'occupied', 'reserved', 'cleaning')
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 23. Kitchen Display System (KDS)
```sql
kds_screens
├── id (PK, BIGINT, Auto Increment)
├── name (VARCHAR)
├── shop_id (BIGINT, FK to shops.id)
├── ip_address (VARCHAR)
├── category_ids (JSON) -- Categories to display
├── is_active (BOOLEAN, Default true)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

#### 24. Enhanced Order States
```sql
order_state_history
├── id (PK, BIGINT, Auto Increment)
├── order_id (BIGINT, FK to orders.id)
├── from_state (VARCHAR, Nullable)
├── to_state (VARCHAR)
├── user_id (BIGINT, FK to users.id, Nullable)
├── notes (TEXT, Nullable)
├── duration_seconds (INT, Nullable) -- Time in previous state
├── created_at (TIMESTAMP)
```

#### 25. Inventory Transactions
```sql
inventory_transactions
├── id (PK, BIGINT, Auto Increment)
├── product_id (BIGINT, FK to products.id)
├── shop_id (BIGINT, FK to shops.id)
├── transaction_type (ENUM: 'sale', 'purchase', 'adjustment', 'wastage', 'return')
├── quantity_change (INT) -- Positive for increase, negative for decrease
├── quantity_before (INT)
├── quantity_after (INT)
├── unit_cost (DECIMAL 8,2, Nullable)
├── reference_type (VARCHAR, Nullable) -- 'order', 'purchase_order', etc.
├── reference_id (BIGINT, Nullable)
├── user_id (BIGINT, FK to users.id)
├── notes (TEXT, Nullable)
├── created_at (TIMESTAMP)
```

#### 26. User Roles and Permissions
```sql
roles
├── id (PK, BIGINT, Auto Increment)
├── name (VARCHAR, Unique)
├── description (TEXT, Nullable)
├── permissions (JSON) -- Array of permissions
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

user_roles
├── id (PK, BIGINT, Auto Increment)
├── user_id (BIGINT, FK to users.id)
├── role_id (BIGINT, FK to roles.id)
├── shop_id (BIGINT, FK to shops.id, Nullable) -- Role per shop
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

## 2026 Enhanced ERD

```
Users ────< Orders >─── Customers
 │         │           │
 │         │           └─── Payments
 │         │
 │         ├─── Order_Items ──── Products
 │         │                    │
 │         │                    ├─── Categories (M-N)
 │         │                    │
 │         │                    └── Discounts (M-N)
 │         │
 │         └─── Discounts (M-N)
 │
 ├─── Tablet_Devices ────< Offline_Orders
 │                      │    │
 │                      │    └─── Sync_Logs
 │                      │
 │                      └─── Product_Snapshots ──── Products
 │
 ├─── User_Roles ──── Roles
 │
 └─── Shop_User (M-N) ──── Shops
                           │
                           ├─── Shop_Product (M-N) ──── Products
                           │
                           ├─── Tables
                           │
                           ├─── KDS_Screens
                           │
                           └── Inventory_Transactions ──── Products
```

## Database Optimization Strategies

### 1. Partitioning
- **Orders Table**: Partition by created_at (monthly)
- **Activity_Log Table**: Partition by created_at (monthly)
- **Inventory_Transactions**: Partition by created_at (quarterly)

### 2. Indexing Strategy
```sql
-- Critical indexes for performance
CREATE INDEX idx_orders_shop_state ON orders(shop_id, state);
CREATE INDEX idx_orders_customer_date ON orders(customer_id, created_at);
CREATE INDEX idx_order_items_order_product ON order_items(order_id, product_id);
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_activity_log_subject_date ON activity_log(subject_type, subject_id, created_at);

-- New 2026 indexes
CREATE INDEX idx_offline_orders_tablet_sync ON offline_orders(tablet_device_id, sync_status);
CREATE INDEX idx_inventory_trans_product_date ON inventory_transactions(product_id, created_at);
```

### 3. Full-Text Search
```sql
-- For product search
ALTER TABLE products ADD FULLTEXT(name, description);

-- For customer search  
ALTER TABLE customers ADD FULLTEXT(name, email, membership_number);
```

### 4. JSON Columns Optimization
```sql
-- Generated columns for frequently accessed JSON data
ALTER TABLE offline_orders 
ADD COLUMN customer_name VARCHAR(255) GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(order_data, '$.customer.name'))) STORED,
ADD INDEX idx_offline_orders_customer_name ON offline_orders(customer_name);
```

## Data Migration Considerations

### 1. Backward Compatibility
- Maintain existing foreign key relationships
- Use views for legacy application compatibility
- Implement feature flags for gradual rollout

### 2. Data Validation
- Referential integrity checks before migration
- Data cleanup scripts for orphaned records
- Performance testing with production-like data volumes

### 3. Rollback Strategy
- Database migration scripts with rollback capability
- Data backup verification procedures
- Point-in-time recovery testing

## Security Enhancements

### 1. Row-Level Security
- Implement shop-based data isolation
- User access control at database level
- Audit trail for all data modifications

### 2. Data Encryption
- Sensitive data encryption at rest
- API communication encryption
- Tablet data encryption for offline storage

This ERD provides the foundation for the 2026 upgrade, supporting offline tablet functionality while maintaining existing POS capabilities and adding new business intelligence features.