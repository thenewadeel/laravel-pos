# Base Business Vue Components Specification

## Overview

This document defines the core reusable Vue components for displaying business objects (orders, items, products, customers) with Role-Based Access Control (RBAC) permissions.

## Component Architecture

```
resources/js/components/business/
├── OrderItemDisplay.vue          # Order line item display
├── OrderCard.vue                 # Order summary card
├── ProductCard.vue               # Product display card
├── CustomerCard.vue              # Customer info card
├── PaymentDisplay.vue            # Payment transaction display
├── StatusBadge.vue               # Reusable status indicator
├── AmountDisplay.vue             # Currency formatted amount
├── TimeDisplay.vue               # Human-readable time
└── index.js                      # Component exports
```

---

## 1. OrderItemDisplay.vue

### Purpose
Display individual order line items with quantity, product info, pricing, and role-based actions.

### Props
```javascript
{
  item: {
    id: Number,
    product_id: Number,
    product_name: String,
    quantity: Number,
    unit_price: Number,
    total_price: Number,
    notes: String,
    status: String // 'pending', 'preparing', 'ready', 'served'
  },
  orderStatus: String, // 'preparing', 'served', 'closed'
  showActions: {
    type: Boolean,
    default: true
  },
  compact: {
    type: Boolean,
    default: false
  }
}
```

### RBAC Permissions
| Action | Admin | Manager | Waiter | Chef | Cashier |
|--------|-------|---------|--------|------|---------|
| View item details | ✅ | ✅ | ✅ | ✅ | ✅ |
| Edit quantity | ✅ | ✅ | ✅ | ❌ | ❌ |
| Delete item | ✅ | ✅ | ✅ | ❌ | ❌ |
| Mark as ready | ✅ | ✅ | ❌ | ✅ | ❌ |
| Add notes | ✅ | ✅ | ✅ | ✅ | ❌ |

### Events
- `update-quantity({ itemId, quantity })` - Update item quantity
- `delete-item(itemId)` - Remove item from order
- `update-status({ itemId, status })` - Change item status
- `add-note({ itemId, note })` - Add special instructions

### Features
- **Visual States**: Pending (gray), Preparing (blue), Ready (green), Served (dark)
- **Quantity Stepper**: +/- buttons for quick adjustment (permission-based)
- **Price Display**: Unit price × Quantity = Total
- **Kitchen Notes**: Special instructions display
- **Compact Mode**: Minimal view for lists

### Template Structure
```vue
<template>
  <div class="order-item" :class="[`status-${item.status}`, { compact }]">
    <div class="item-main">
      <div class="item-quantity" v-if="canEditQuantity">
        <button @click="decrement" :disabled="item.quantity <= 1">-</button>
        <span>{{ item.quantity }}</span>
        <button @click="increment">+</button>
      </div>
      <div class="item-quantity" v-else>
        <span class="qty-badge">{{ item.quantity }}×</span>
      </div>
      
      <div class="item-details">
        <div class="product-name">{{ item.product_name }}</div>
        <div class="product-meta">
          <span class="unit-price">${{ formatPrice(item.unit_price) }}</span>
          <span v-if="item.notes" class="item-notes">
            <i class="fas fa-comment"></i> {{ item.notes }}
          </span>
        </div>
      </div>
      
      <div class="item-total">${{ formatPrice(item.total_price) }}</div>
    </div>
    
    <div class="item-actions" v-if="showActions && hasAnyPermission">
      <button v-if="canEditQuantity" @click="editQuantity" class="btn-icon">
        <i class="fas fa-edit"></i>
      </button>
      <button v-if="canDelete" @click="confirmDelete" class="btn-icon danger">
        <i class="fas fa-trash"></i>
      </button>
      <button v-if="canUpdateStatus && item.status !== 'ready'" 
              @click="markReady" 
              class="btn-icon success">
        <i class="fas fa-check"></i>
      </button>
    </div>
  </div>
</template>
```

---

## 2. OrderCard.vue

### Purpose
Summary card displaying order information for lists and dashboards.

### Props
```javascript
{
  order: {
    id: Number,
    POS_number: String,
    table_number: String,
    waiter_name: String,
    customer: Object,
    type: String, // 'dine-in', 'take-away', 'delivery'
    state: String, // 'preparing', 'served', 'closed'
    total_amount: Number,
    item_count: Number,
    created_at: String,
    updated_at: String
  },
  showDetails: {
    type: Boolean,
    default: true
  },
  selectable: {
    type: Boolean,
    default: false
  }
}
```

### RBAC Permissions
| Action | Admin | Manager | Waiter | Chef | Cashier |
|--------|-------|---------|--------|------|---------|
| View full details | ✅ | ✅ | ✅ | ✅ | ✅ |
| Edit order | ✅ | ✅ | ✅ | ❌ | ❌ |
| Cancel order | ✅ | ✅ | ✅ | ❌ | ❌ |
| Process payment | ✅ | ✅ | ❌ | ❌ | ✅ |
| Assign to table | ✅ | ✅ | ✅ | ❌ | ❌ |
| View customer info | ✅ | ✅ | ✅ | ❌ | ✅ |

### Events
- `click(order)` - Card clicked
- `edit(order)` - Edit order
- `cancel(order)` - Cancel order
- `pay(order)` - Process payment
- `assign(order)` - Assign to table

### Features
- **Type Icons**: Dine-in (🍽️), Take-away (📦), Delivery (🚚)
- **Status Colors**: Preparing (blue), Served (green), Closed (gray)
- **Quick Actions**: Context-aware buttons based on status
- **Time Display**: Relative time (e.g., "5 mins ago")
- **Customer Avatar**: Initials or photo

---

## 3. ProductCard.vue

### Purpose
Display product information with inventory status and quick actions.

### Props
```javascript
{
  product: {
    id: Number,
    name: String,
    description: String,
    price: Number,
    quantity: Number,
    category: Object,
    image_url: String,
    is_available: Boolean,
    low_stock_threshold: Number
  },
  showInventory: {
    type: Boolean,
    default: true
  },
  selectable: {
    type: Boolean,
    default: false
  }
}
```

### RBAC Permissions
| Action | Admin | Manager | Waiter | Chef | Cashier |
|--------|-------|---------|--------|------|---------|
| View product | ✅ | ✅ | ✅ | ✅ | ✅ |
| Edit product | ✅ | ✅ | ❌ | ❌ | ❌ |
| View inventory | ✅ | ✅ | ✅ | ✅ | ❌ |
| Adjust stock | ✅ | ✅ | ❌ | ❌ | ❌ |
| Toggle availability | ✅ | ✅ | ❌ | ✅ | ❌ |
| Add to order | ✅ | ✅ | ✅ | ❌ | ❌ |

### Features
- **Stock Indicators**: In Stock (green), Low Stock (yellow), Out of Stock (red)
- **Category Badge**: Category name with color
- **Quick Add**: + button to add to current order
- **Image Display**: Product photo with fallback
- **Price Formatting**: Currency with proper decimals

---

## 4. CustomerCard.vue

### Purpose
Customer information display with order history and contact details.

### Props
```javascript
{
  customer: {
    id: Number,
    name: String,
    phone: String,
    email: String,
    membership_number: String,
    loyalty_points: Number,
    tier: String, // 'bronze', 'silver', 'gold', 'platinum'
    total_orders: Number,
    total_spent: Number,
    last_order_at: String
  },
  showHistory: {
    type: Boolean,
    default: true
  },
  compact: {
    type: Boolean,
    default: false
  }
}
```

### RBAC Permissions
| Action | Admin | Manager | Waiter | Chef | Cashier |
|--------|-------|---------|--------|------|---------|
| View basic info | ✅ | ✅ | ✅ | ❌ | ✅ |
| View contact details | ✅ | ✅ | ✅ | ❌ | ✅ |
| View order history | ✅ | ✅ | ✅ | ❌ | ✅ |
| View loyalty points | ✅ | ✅ | ❌ | ❌ | ✅ |
| Edit customer | ✅ | ✅ | ❌ | ❌ | ❌ |
| Create order | ✅ | ✅ | ✅ | ❌ | ✅ |

### Features
- **Tier Badge**: Bronze/Silver/Gold/Platinum with colors
- **Avatar**: Initials with tier-colored background
- **Quick Contact**: Click to call/email
- **Order Stats**: Total orders and lifetime value
- **VIP Indicator**: Special styling for high-tier customers

---

## 5. StatusBadge.vue

### Purpose
Reusable status indicator with consistent styling across the app.

### Props
```javascript
{
  status: String,
  type: {
    type: String,
    default: 'order' // 'order', 'table', 'payment', 'sync'
  },
  size: {
    type: String,
    default: 'medium' // 'small', 'medium', 'large'
  },
  showIcon: {
    type: Boolean,
    default: true
  }
}
```

### Status Mappings
```javascript
const statusConfig = {
  order: {
    preparing: { color: 'blue', icon: 'fa-spinner', label: 'Preparing' },
    served: { color: 'green', icon: 'fa-check', label: 'Served' },
    closed: { color: 'gray', icon: 'fa-lock', label: 'Closed' },
    wastage: { color: 'red', icon: 'fa-trash', label: 'Wastage' }
  },
  table: {
    available: { color: 'green', icon: 'fa-check-circle', label: 'Available' },
    occupied: { color: 'red', icon: 'fa-user', label: 'Occupied' },
    reserved: { color: 'yellow', icon: 'fa-clock', label: 'Reserved' },
    cleaning: { color: 'blue', icon: 'fa-broom', label: 'Cleaning' }
  },
  payment: {
    pending: { color: 'orange', icon: 'fa-clock', label: 'Pending' },
    paid: { color: 'green', icon: 'fa-check', label: 'Paid' },
    refunded: { color: 'purple', icon: 'fa-undo', label: 'Refunded' }
  },
  sync: {
    synced: { color: 'green', icon: 'fa-sync', label: 'Synced' },
    pending: { color: 'orange', icon: 'fa-hourglass', label: 'Pending' },
    conflict: { color: 'red', icon: 'fa-exclamation', label: 'Conflict' }
  }
}
```

---

## 6. AmountDisplay.vue

### Purpose
Consistent currency display with formatting and color coding.

### Props
```javascript
{
  amount: Number,
  currency: {
    type: String,
    default: 'USD'
  },
  showSign: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'medium' // 'small', 'medium', 'large'
  },
  color: {
    type: String,
    default: 'auto' // 'auto', 'positive', 'negative', 'neutral'
  }
}
```

### Features
- **Auto-coloring**: Positive (green), Negative (red), Zero (gray)
- **Currency Symbol**: $, €, £ based on currency prop
- **Decimal Places**: Always shows 2 decimals
- **Size Variants**: Small (14px), Medium (16px), Large (24px)

---

## RBAC Role Definitions

### User Types
```javascript
const roles = {
  admin: {
    level: 5,
    permissions: ['*'], // All permissions
    description: 'Full system access'
  },
  manager: {
    level: 4,
    permissions: [
      'orders.view', 'orders.edit', 'orders.cancel',
      'products.view', 'products.edit', 'products.stock',
      'customers.view', 'customers.edit',
      'tables.view', 'tables.assign', 'tables.manage',
      'payments.view', 'payments.process',
      'reports.view'
    ],
    description: 'Manage operations and staff'
  },
  waiter: {
    level: 3,
    permissions: [
      'orders.view', 'orders.create', 'orders.edit.own',
      'products.view',
      'customers.view',
      'tables.view', 'tables.assign'
    ],
    description: 'Take orders and serve customers'
  },
  chef: {
    level: 2,
    permissions: [
      'orders.view', 'orders.update_status',
      'products.view', 'products.availability'
    ],
    description: 'Prepare orders and manage kitchen'
  },
  cashier: {
    level: 2,
    permissions: [
      'orders.view',
      'customers.view',
      'payments.view', 'payments.process',
      'reports.view.limited'
    ],
    description: 'Process payments and close orders'
  }
}
```

### Permission Helper
```javascript
// composables/usePermissions.js
export function usePermissions() {
  const user = computed(() => store.state.auth.user)
  
  const can = (permission) => {
    if (user.value?.type === 'admin') return true
    return user.value?.permissions?.includes(permission)
  }
  
  const canAny = (permissions) => {
    return permissions.some(p => can(p))
  }
  
  const canAll = (permissions) => {
    return permissions.every(p => can(p))
  }
  
  return {
    can,
    canAny,
    canAll,
    user
  }
}
```

---

## Test Checklist

### OrderItemDisplay Tests
- [ ] Renders item details correctly (product name, quantity, price)
- [ ] Shows correct status badge color
- [ ] Quantity stepper visible only with edit permission
- [ ] Delete button visible only with delete permission
- [ ] Status update button visible only for chef role
- [ ] Emits correct events on actions
- [ ] Compact mode hides actions and metadata
- [ ] Formats prices with 2 decimal places

### OrderCard Tests
- [ ] Displays order number, table, waiter, and type
- [ ] Shows correct status color and icon
- [ ] Displays customer name or "Walk-in"
- [ ] Shows item count and total amount
- [ ] Edit button visible for admin/manager/waiter
- [ ] Pay button visible only for cashier
- [ ] Emits click event with order data
- [ ] Relative time display updates correctly

### ProductCard Tests
- [ ] Shows product image or fallback
- [ ] Displays name, description, and price
- [ ] Stock indicator shows correct color
- [ ] Category badge displays with correct color
- [ ] Quick add button visible for waiter/manager
- [ ] Edit button visible only for admin/manager
- [ ] Availability toggle visible only for admin/chef

### CustomerCard Tests
- [ ] Displays customer name and avatar
- [ ] Shows tier badge with correct color
- [ ] Contact info visible based on permissions
- [ ] Order history visible for authorized roles
- [ ] Loyalty points hidden from waiters
- [ ] VIP indicator shows for platinum/gold
- [ ] Click-to-call/email works

### StatusBadge Tests
- [ ] Renders correct icon for each status
- [ ] Applies correct color class
- [ ] Shows label text
- [ ] Size variants work correctly
- [ ] Icon can be hidden
- [ ] All status types supported (order, table, payment, sync)

### AmountDisplay Tests
- [ ] Formats with currency symbol
- [ ] Shows 2 decimal places
- [ ] Auto-colors positive/negative/zero
- [ ] Respects color override prop
- [ ] Size variants render correctly
- [ ] Handles null/undefined amounts

### RBAC Permission Tests
- [ ] Admin can access all features
- [ ] Manager can access operational features
- [ ] Waiter can only access order-related features
- [ ] Chef can only access kitchen-related features
- [ ] Cashier can only access payment features
- [ ] Unauthorized actions are hidden
- [ ] Permission helper functions work correctly

---

## Implementation Priority

### Phase 1: Core Components
1. StatusBadge.vue (foundation)
2. AmountDisplay.vue (foundation)
3. OrderItemDisplay.vue (most used)
4. OrderCard.vue (most used)

### Phase 2: Business Objects
5. ProductCard.vue
6. CustomerCard.vue
7. PaymentDisplay.vue

### Phase 3: Advanced Features
8. TimeDisplay.vue
9. Permission composables
10. Component tests

---

## Success Criteria

- [ ] All components render correctly with test data
- [ ] RBAC permissions properly restrict actions
- [ ] Components are reusable across different views
- [ ] Consistent styling and behavior
- [ ] All test checklist items pass
- [ ] Documentation is complete and accurate
- [ ] Components work with existing floor management system
