# Vue.js Floor Management Interface

## Overview

This document specifies the Vue.js interface for the Floor Management System, providing a unified interface for both floor management and business operations with role-based access control.

## Component Architecture

```
resources/js/components/floor-management/
├── FloorManagement.vue           # Main container (route component)
├── FloorSelector.vue             # Floor tabs/selector sidebar
├── FloorLayout.vue               # Drag-and-drop floor canvas
├── TableComponent.vue            # Individual table representation
├── TableDetails.vue              # Table info and order panel (sidebar)
├── FloorToolbar.vue              # Admin tools for floor editing
├── TableStatusBadge.vue          # Status indicator component
├── OrderAssignmentModal.vue      # Modal for assigning orders to tables
└── FloorManager.vue              # Admin floor configuration view
```

## Component Specifications

### 1. FloorManagement.vue
**Purpose**: Main container component, handles data loading and state management

**Props**:
- `shopId`: Number - Current shop ID
- `user`: Object - Current user with type/permissions

**Data**:
- `floors`: Array - List of floors with tables
- `activeFloorId`: Number - Currently selected floor
- `selectedTable`: Object - Currently selected table
- `isLoading`: Boolean - Loading state
- `isEditMode`: Boolean - Edit mode for admin

**Computed**:
- `activeFloor`: Object - Current floor object
- `canManageFloors`: Boolean - User has admin role
- `canAssignOrders`: Boolean - User can assign orders

**Methods**:
- `loadFloors()`: Load floor data from API
- `selectFloor(floorId)`: Change active floor
- `selectTable(table)`: Select a table
- `refreshData()`: Refresh floor data

**Template Structure**:
```vue
<template>
  <div class="floor-management">
    <FloorSelector 
      :floors="floors" 
      :active-floor-id="activeFloorId"
      @select="selectFloor" />
    
    <div class="floor-content">
      <FloorToolbar 
        v-if="canManageFloors"
        :is-edit-mode="isEditMode"
        @toggle-edit="toggleEditMode"
        @add-table="showAddTableModal"
        @save-layout="saveLayout" />
      
      <FloorLayout 
        :floor="activeFloor"
        :tables="activeFloor?.tables"
        :is-edit-mode="isEditMode"
        :selected-table="selectedTable"
        @select-table="selectTable"
        @update-table-position="updateTablePosition" />
    </div>
    
    <TableDetails 
      :table="selectedTable"
      :can-assign="canAssignOrders"
      @assign-order="showAssignOrderModal"
      @release-table="releaseTable"
      @update-status="updateTableStatus" />
  </div>
</template>
```

### 2. FloorSelector.vue
**Purpose**: Sidebar floor navigation

**Props**:
- `floors`: Array - Available floors
- `activeFloorId`: Number - Currently active floor

**Events**:
- `select(floorId)`: Floor selected

**Features**:
- Floor tabs with names
- Visual indicator for active floor
- Badge showing available table count
- Sortable floors (admin only)

### 3. FloorLayout.vue
**Purpose**: Visual floor plan with draggable tables

**Props**:
- `floor`: Object - Floor data
- `tables`: Array - Tables on this floor
- `isEditMode`: Boolean - Enable drag/resize
- `selectedTable`: Object - Currently selected

**Events**:
- `select-table(table)`: Table clicked
- `update-table-position(table, x, y)`: Position changed

**Features**:
- Canvas with floor background
- Tables positioned absolutely
- Drag-and-drop (edit mode)
- Resize handles (edit mode)
- Click to select
- Status color coding:
  - 🟢 Available: Green
  - 🔴 Occupied: Red
  - 🟡 Reserved: Yellow
  - 🔵 Cleaning: Blue
  - ⚫ Maintenance: Gray

**Table Shapes**:
- Rectangle: Default
- Circle: Border-radius 50%
- Oval: Border-radius 50% with different width/height

### 4. TableComponent.vue
**Purpose**: Individual table representation

**Props**:
- `table`: Object - Table data
- `isSelected`: Boolean - Selected state
- `isEditMode`: Boolean - Edit mode enabled

**Events**:
- `click`: Table clicked
- `drag-start`: Start dragging
- `drag-end`: Stop dragging

**Computed**:
- `tableStyle`: CSS positioning and sizing
- `statusColor`: Color based on status
- `displayName`: Table number or name

**Template**:
```vue
<template>
  <div 
    class="table-component"
    :class="[`shape-${table.shape}`, `status-${table.status}`, { selected: isSelected }]"
    :style="tableStyle"
    @click="$emit('click')">
    
    <TableStatusBadge :status="table.status" />
    
    <div class="table-info">
      <span class="table-number">{{ table.table_number }}</span>
      <span v-if="table.name" class="table-name">{{ table.name }}</span>
      <span class="table-capacity">
        <i class="fas fa-users"></i> {{ table.capacity }}
      </span>
    </div>
    
    <!-- Resize handles (edit mode only) -->
    <template v-if="isEditMode">
      <div class="resize-handle resize-se" @mousedown.stop="startResize('se')" />
      <div class="resize-handle resize-sw" @mousedown.stop="startResize('sw')" />
      <div class="resize-handle resize-ne" @mousedown.stop="startResize('ne')" />
      <div class="resize-handle resize-nw" @mousedown.stop="startResize('nw')" />
    </template>
  </div>
</template>
```

### 5. TableDetails.vue
**Purpose**: Sidebar panel showing table details and actions

**Props**:
- `table`: Object - Selected table
- `canAssign`: Boolean - Can assign orders

**Events**:
- `assign-order`: Show assignment modal
- `release-table`: Release table
- `update-status(status)`: Update status

**Features**:
- Table information display
- Current order details (if occupied)
- Action buttons based on status:
  - Available: "Assign Order"
  - Occupied: "View Order", "Release Table"
  - Reserved: "View Reservation", "Cancel"
  - Cleaning: "Mark Available"
  - Maintenance: "Mark Available"
- Occupation timer (if occupied)
- Order history (last 5 orders)

### 6. OrderAssignmentModal.vue
**Purpose**: Modal for assigning orders to tables

**Props**:
- `table`: Object - Table to assign
- `visible`: Boolean - Show/hide modal

**Events**:
- `assign(orderId)`: Assign order
- `close`: Close modal

**Features**:
- Search orders by number/customer
- Filter by order status
- Show order details
- Confirm assignment
- Validation (table capacity vs party size)

### 7. FloorToolbar.vue
**Purpose**: Admin toolbar for floor editing

**Props**:
- `isEditMode`: Boolean - Current edit state

**Events**:
- `toggle-edit`: Toggle edit mode
- `add-table`: Add new table
- `save-layout`: Save current layout
- `undo`: Undo last change
- `redo`: Redo last change

**Features** (Admin only):
- Edit mode toggle
- Add table button
- Save layout button
- Undo/Redo buttons
- Grid snap toggle
- Zoom controls

## State Management

### Pinia Store: floorManager

```javascript
// stores/floorManager.js
export const useFloorStore = defineStore('floor', {
  state: () => ({
    floors: [],
    activeFloorId: null,
    selectedTableId: null,
    isLoading: false,
    isEditMode: false,
    syncStatus: 'synced',
  }),
  
  getters: {
    activeFloor: (state) => state.floors.find(f => f.id === state.activeFloorId),
    selectedTable: (state) => {
      const floor = state.floors.find(f => f.id === state.activeFloorId);
      return floor?.tables.find(t => t.id === state.selectedTableId);
    },
    availableTables: (state) => {
      const floor = state.floors.find(f => f.id === state.activeFloorId);
      return floor?.tables.filter(t => t.status === 'available') || [];
    },
  },
  
  actions: {
    async loadFloors(shopId) {
      this.isLoading = true;
      const response = await axios.get(`/api/v1/floors?shop_id=${shopId}`);
      this.floors = response.data.data.floors;
      this.isLoading = false;
    },
    
    async assignOrder(tableId, orderId) {
      await axios.post(`/api/v1/tables/${tableId}/assign-order`, { order_id: orderId });
      await this.loadFloors(this.activeFloor.shop_id);
    },
    
    async updateTablePosition(tableId, x, y) {
      await axios.put(`/api/v1/tables/${tableId}`, { position_x: x, position_y: y });
    },
    
    selectFloor(floorId) {
      this.activeFloorId = floorId;
      this.selectedTableId = null;
    },
    
    selectTable(tableId) {
      this.selectedTableId = tableId;
    },
  }
});
```

## Role-Based Access Control

### Permission Helper
```javascript
// utils/permissions.js
export const usePermissions = (user) => {
  return {
    canManageFloors: computed(() => user.type === 'admin'),
    canManageTables: computed(() => ['admin', 'manager'].includes(user.type)),
    canAssignOrders: computed(() => ['admin', 'manager', 'waiter', 'cashier'].includes(user.type)),
    canViewFloor: computed(() => true), // All authenticated users
    canEditLayout: computed(() => user.type === 'admin'),
  };
};
```

### UI Visibility Rules
- **FloorSelector**: Visible to all
- **FloorToolbar**: Admin only
- **Table drag/resize**: Admin edit mode only
- **Assign Order button**: Waiter, Cashier, Manager, Admin
- **Release Table button**: Manager, Admin, assigned waiter
- **Edit floor structure**: Admin only

## Offline Support

### Service Worker Integration
```javascript
// Register floor data for offline caching
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.ready.then(registration => {
    registration.active.postMessage({
      type: 'CACHE_FLOOR_DATA',
      url: '/api/v1/sync/floors'
    });
  });
}
```

### Sync Queue
```javascript
// Queue actions when offline
const syncQueue = [];

const queueAction = (action) => {
  if (!navigator.onLine) {
    syncQueue.push(action);
    localStorage.setItem('floorSyncQueue', JSON.stringify(syncQueue));
  }
};

// Process queue when back online
window.addEventListener('online', () => {
  const queue = JSON.parse(localStorage.getItem('floorSyncQueue') || '[]');
  queue.forEach(action => processSyncAction(action));
  localStorage.removeItem('floorSyncQueue');
});
```

## Responsive Design

### Breakpoints
- **Desktop (>1024px)**: Full layout with sidebar
- **Tablet (768-1024px)**: Collapsible sidebar, touch-optimized
- **Mobile (<768px)**: List view instead of floor plan

### Mobile List View
```vue
<template v-if="isMobile">
  <div class="mobile-table-list">
    <div 
      v-for="table in activeFloor.tables" 
      :key="table.id"
      class="table-list-item"
      :class="`status-${table.status}`"
      @click="selectTable(table)">
      <div class="table-info">
        <h4>{{ table.table_number }}</h4>
        <p v-if="table.name">{{ table.name }}</p>
      </div>
      <TableStatusBadge :status="table.status" />
    </div>
  </div>
</template>
```

## Real-Time Updates

### WebSocket Integration
```javascript
// Echo channel for floor updates
Echo.channel(`floor.${floorId}`)
  .listen('TableStatusChanged', (e) => {
    floorStore.updateTableStatus(e.table_id, e.status);
  })
  .listen('TableAssigned', (e) => {
    floorStore.refreshTable(e.table_id);
  });
```

### Polling Fallback
```javascript
// Poll for updates every 30 seconds
const startPolling = () => {
  setInterval(() => {
    floorStore.checkForUpdates();
  }, 30000);
};
```

## Implementation Steps

1. **Create base components** (FloorManagement, FloorSelector)
2. **Implement FloorLayout with drag-and-drop**
3. **Add TableComponent with status indicators**
4. **Create TableDetails sidebar**
5. **Add OrderAssignmentModal**
6. **Implement role-based permissions**
7. **Add offline support**
8. **Implement real-time updates**
9. **Add responsive design**
10. **Write component tests**

## Testing Strategy

### Component Tests
- FloorManagement data loading
- FloorSelector floor switching
- FloorLayout drag-and-drop
- TableComponent status display
- TableDetails actions
- OrderAssignmentModal validation

### E2E Tests
- Complete order-to-table workflow
- Offline mode operation
- Role-based access verification
- Real-time update handling
