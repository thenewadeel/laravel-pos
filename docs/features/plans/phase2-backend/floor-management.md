# Floor Management System Specification

## Task ID: P2-BE-004  
**Phase**: Phase 2 - Backend Modernization  
**Priority**: Critical  
**Estimated Hours**: 48 hours  
**Dependencies**: P2-BE-003 (API Standardization)

---

## Overview

This document specifies a unified Floor Management System that integrates with existing POS operations. The system manages restaurant floors, tables, and their association with orders, providing both floor management and business operations in a single interface.

## Business Requirements

### Core Concepts
- **Floor**: A physical level or section of the restaurant (e.g., "Ground Floor", "Terrace", "VIP Section")
- **Table**: A dining table located on a specific floor
- **Table Status**: Available, Occupied, Reserved, Cleaning, Maintenance
- **Order Association**: Tables can have active orders; orders can be assigned to tables

### User Roles & Permissions

#### Admin
- Full floor management (CRUD floors and tables)
- View all floor layouts
- Assign tables to orders
- Configure table properties

#### Manager
- View floor layouts
- Manage table status
- Assign tables to orders
- Cannot delete floors/tables

#### Waiter/Cashier
- View floor layouts
- Assign orders to tables
- Update table status (occupied, cleaning)
- Cannot modify floor/table structure

#### Kitchen Staff
- View table orders
- Update order status
- No floor management access

## Database Schema

### Tables

#### floors
```sql
CREATE TABLE floors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shop_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    layout_config JSON NULL, -- Store layout data (positions, sizes)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE,
    INDEX idx_shop_id (shop_id),
    INDEX idx_sort_order (sort_order)
);
```

#### restaurant_tables
```sql
CREATE TABLE restaurant_tables (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    floor_id BIGINT UNSIGNED NOT NULL,
    table_number VARCHAR(50) NOT NULL,
    name VARCHAR(100) NULL,
    capacity INT DEFAULT 4,
    status ENUM('available', 'occupied', 'reserved', 'cleaning', 'maintenance') DEFAULT 'available',
    position_x DECIMAL(10,2) DEFAULT 0,
    position_y DECIMAL(10,2) DEFAULT 0,
    width DECIMAL(10,2) DEFAULT 100,
    height DECIMAL(10,2) DEFAULT 100,
    shape ENUM('rectangle', 'circle', 'oval') DEFAULT 'rectangle',
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSON NULL, -- Additional table properties
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (floor_id) REFERENCES floors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_table_per_floor (floor_id, table_number),
    INDEX idx_floor_id (floor_id),
    INDEX idx_status (status)
);
```

#### table_orders (junction table for many-to-many)
```sql
CREATE TABLE table_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_active_table_order (table_id, order_id, is_active),
    INDEX idx_table_id (table_id),
    INDEX idx_order_id (order_id)
);
```

## API Endpoints

### Floor Management

```php
// Floors
GET    /api/v1/floors              - List all floors for current shop
GET    /api/v1/floors/{id}         - Get specific floor with tables
POST   /api/v1/floors              - Create new floor (Admin only)
PUT    /api/v1/floors/{id}         - Update floor (Admin only)
DELETE /api/v1/floors/{id}         - Delete floor (Admin only)

// Tables
GET    /api/v1/floors/{id}/tables  - List all tables on a floor
POST   /api/v1/floors/{id}/tables  - Create table on floor (Admin only)
PUT    /api/v1/tables/{id}         - Update table (Admin/Manager)
DELETE /api/v1/tables/{id}         - Delete table (Admin only)
PATCH  /api/v1/tables/{id}/status  - Update table status

// Table Operations
POST   /api/v1/tables/{id}/assign-order    - Assign order to table
POST   /api/v1/tables/{id}/release         - Release table (mark available)
GET    /api/v1/tables/{id}/active-order    - Get active order for table

// Floor Layout
GET    /api/v1/floors/{id}/layout   - Get floor layout configuration
PUT    /api/v1/floors/{id}/layout   - Update floor layout (Admin only)

// Offline Sync
POST   /api/v1/sync/floors          - Sync floor data for offline tablets
GET    /api/v1/sync/floors/status   - Get floor sync status
```

## Vue.js Interface Specification

### Components Structure

```
resources/js/components/floor-management/
├── FloorManagement.vue           # Main container component
├── FloorSelector.vue             # Floor tabs/selector
├── FloorLayout.vue               # Drag-and-drop floor layout
├── TableComponent.vue            # Individual table representation
├── TableDetails.vue              # Table info and order panel
├── FloorToolbar.vue              # Tools for floor editing
├── TableStatusBadge.vue          # Status indicator component
└── OrderAssignmentModal.vue      # Modal for assigning orders
```

### Key Features

#### 1. Floor View
- Visual floor plan with draggable tables
- Table status color coding:
  - 🟢 Available (Green)
  - 🔴 Occupied (Red)
  - 🟡 Reserved (Yellow)
  - 🔵 Cleaning (Blue)
  - ⚫ Maintenance (Gray)
- Click table to view details/assign order
- Real-time status updates via WebSocket

#### 2. Table Management (Admin/Manager)
- Add/edit/delete tables
- Drag to reposition tables
- Resize tables
- Change table properties (capacity, shape, number)
- Batch operations (select multiple tables)

#### 3. Order Integration
- Assign new order to table
- View active order details
- Quick actions (add items, process payment)
- Table timer (how long occupied)

#### 4. Role-Based UI
```javascript
// Permission checks
const canManageFloors = user.type === 'admin';
const canManageTables = ['admin', 'manager'].includes(user.type);
const canAssignOrders = ['admin', 'manager', 'waiter', 'cashier'].includes(user.type);
const canViewFloor = true; // All authenticated users
```

### Responsive Design
- Desktop: Full floor layout with sidebar
- Tablet: Optimized touch interface
- Mobile: List view with floor filter

## Offline Support

### Sync Strategy
1. **Download**: Tablet downloads all floor/table data on login
2. **Updates**: Incremental sync for changes (new orders, status updates)
3. **Conflict Resolution**: Server wins for floor structure, timestamp wins for status
4. **Queue**: Offline table assignments queued for sync

### Offline Capabilities
- View floor layout (cached)
- Assign orders to tables (queued)
- Update table status (queued)
- Cannot modify floor/table structure offline

## Implementation Tasks

### Phase 1: Database & Models (8 hours)
- [ ] Create migration files
- [ ] Create Floor model with relationships
- [ ] Create RestaurantTable model with relationships
- [ ] Create TableOrder pivot model
- [ ] Add relationships to existing Order model
- [ ] Write unit tests for models

### Phase 2: API Development (16 hours)
- [ ] Create FloorController with CRUD
- [ ] Create TableController with CRUD
- [ ] Create TableAssignmentController
- [ ] Implement request validation
- [ ] Add API resources for serialization
- [ ] Implement offline sync endpoints
- [ ] Write feature tests for all endpoints

### Phase 3: Business Logic (8 hours)
- [ ] Table status management service
- [ ] Order assignment service
- [ ] Floor layout management
- [ ] Conflict resolution for offline sync
- [ ] Write service tests

### Phase 4: Vue.js Interface (16 hours)
- [ ] Set up Vue components structure
- [ ] Create FloorManagement main component
- [ ] Implement FloorLayout with drag-and-drop
- [ ] Create TableComponent with status indicators
- [ ] Add OrderAssignmentModal
- [ ] Implement role-based UI controls
- [ ] Add real-time updates (WebSocket/Polling)
- [ ] Write component tests

## Testing Strategy

### Unit Tests
- Floor model relationships and scopes
- RestaurantTable model relationships and status management
- Table assignment logic
- Permission checks

### Feature Tests
- Floor CRUD operations
- Table CRUD operations
- Order assignment workflow
- API authentication/authorization
- Offline sync endpoints

### Integration Tests
- End-to-end order-to-table workflow
- Offline sync scenario
- Concurrent table assignment (race conditions)

## Success Metrics

- **Test Coverage**: >90% for models, >85% for controllers
- **API Response Time**: <200ms for floor data
- **UI Performance**: <100ms for table interactions
- **Offline Sync**: <5 seconds for full floor data
- **User Adoption**: >95% of orders assigned to tables

## Future Enhancements

1. **Reservation System**: Book tables in advance
2. **Waitlist Management**: Queue customers when tables full
3. **Analytics**: Table turnover rates, popular tables
4. **Mobile App**: Dedicated waiter app for table management
5. **QR Code Integration**: Customers scan table QR to view menu/order

---

## Next Steps

1. Create database migrations
2. Write failing tests for Floor model
3. Implement Floor model
4. Write failing tests for Table model
5. Implement Table model
6. Continue with API and Vue.js components
