# Base Business Components - Test Checklist

## Component Test Requirements

### 1. StatusBadge.vue
**Test File**: `tests/Unit/Vue/StatusBadge.spec.js`

#### Rendering Tests
- [x] Renders with default props
- [x] Displays correct icon for 'preparing' status
- [x] Displays correct icon for 'served' status
- [x] Displays correct icon for 'closed' status
- [x] Displays correct icon for 'available' table status
- [x] Displays correct icon for 'occupied' table status
- [x] Applies correct color class for each status
- [x] Shows label text when showLabel is true
- [x] Hides label text when showLabel is false
- [x] Renders small size variant
- [x] Renders medium size variant
- [x] Renders large size variant
- [x] Hides icon when showIcon is false

#### Status Type Tests
- [x] Handles 'order' type statuses
- [x] Handles 'table' type statuses
- [x] Handles 'payment' type statuses
- [x] Handles 'sync' type statuses
- [x] Defaults to 'order' type when not specified

#### Edge Cases
- [x] Handles unknown status gracefully
- [x] Handles empty status prop
- [x] Handles null/undefined status

---

### 2. AmountDisplay.vue
**Test File**: `tests/Unit/Vue/AmountDisplay.spec.js`

#### Formatting Tests
- [x] Displays USD currency symbol by default
- [x] Displays EUR currency symbol when specified
- [x] Shows 2 decimal places for whole numbers
- [x] Shows 2 decimal places for decimals
- [x] Formats 0 as "$0.00"
- [x] Formats negative amounts correctly
- [x] Formats large amounts with commas

#### Color Tests
- [x] Auto-colors positive amounts green
- [x] Auto-colors negative amounts red
- [x] Auto-colors zero amounts gray
- [x] Respects 'positive' color override
- [x] Respects 'negative' color override
- [x] Respects 'neutral' color override

#### Size Tests
- [x] Renders small size (14px)
- [x] Renders medium size (16px)
- [x] Renders large size (24px)

#### Edge Cases
- [x] Handles null amount
- [x] Handles undefined amount
- [x] Handles string number input
- [x] Renders with showSign false

---

### 3. OrderItemDisplay.vue
**Test File**: `tests/Unit/Vue/OrderItemDisplay.spec.js`

#### Rendering Tests
- [x] Renders product name correctly
- [x] Displays quantity badge
- [x] Shows unit price formatted
- [x] Shows total price calculated
- [x] Displays notes when present
- [x] Hides notes section when empty
- [x] Applies status color class
- [x] Renders compact mode correctly

#### Permission-Based Tests (RBAC)
- [x] Admin: Shows all action buttons
- [x] Manager: Shows all action buttons
- [x] Waiter: Shows edit and delete buttons
- [x] Chef: Shows status update button only
- [x] Cashier: Shows no action buttons
- [x] Edit button hidden without 'orders.edit' permission
- [x] Delete button hidden without 'orders.delete' permission
- [x] Status button hidden without 'orders.update_status' permission

#### Interaction Tests
- [x] Emits 'update-quantity' on increment
- [x] Emits 'update-quantity' on decrement
- [x] Emits 'delete-item' on delete click
- [x] Emits 'update-status' on status change
- [x] Emits 'add-note' on note add
- [x] Shows confirmation before delete
- [x] Disables decrement at quantity 1

#### Status Tests
- [x] Shows 'pending' status styling
- [x] Shows 'preparing' status styling
- [x] Shows 'ready' status styling
- [x] Shows 'served' status styling

---

### 4. OrderCard.vue
**Test File**: `tests/Unit/Vue/OrderCard.spec.js`

#### Rendering Tests
- [x] Displays POS number prominently
- [x] Shows table number
- [x] Shows waiter name
- [x] Displays order type icon (dine-in)
- [x] Displays order type icon (take-away)
- [x] Displays order type icon (delivery)
- [x] Shows customer name
- [x] Shows "Walk-in" when no customer
- [x] Displays item count
- [x] Shows total amount formatted
- [x] Shows relative time (e.g., "5 mins ago")
- [x] Applies status color correctly

#### Permission-Based Tests (RBAC)
- [x] Admin: Shows edit, cancel, pay, assign buttons
- [x] Manager: Shows edit, cancel, assign buttons
- [x] Waiter: Shows edit button only
- [x] Chef: Shows no action buttons
- [x] Cashier: Shows pay button only
- [x] Edit button requires 'orders.edit' permission
- [x] Cancel button requires 'orders.cancel' permission
- [x] Pay button requires 'payments.process' permission

#### Interaction Tests
- [x] Emits 'click' event with order data
- [x] Emits 'edit' event on edit click
- [x] Emits 'cancel' event on cancel click
- [x] Emits 'pay' event on pay click
- [x] Emits 'assign' event on assign click
- [x] Shows confirmation on cancel

#### State Tests
- [x] Shows correct actions for 'preparing' status
- [x] Shows correct actions for 'served' status
- [x] Shows correct actions for 'closed' status

---

### 5. ProductCard.vue
**Test File**: `tests/Unit/Vue/ProductCard.spec.js`

#### Rendering Tests
- [x] Displays product image
- [x] Shows fallback when no image
- [x] Shows product name
- [x] Shows description (truncated if long)
- [x] Displays price formatted
- [x] Shows category badge
- [x] Displays stock quantity

#### Stock Indicator Tests
- [x] Shows 'in stock' (green) when quantity > threshold
- [x] Shows 'low stock' (yellow) when quantity <= threshold
- [x] Shows 'out of stock' (red) when quantity = 0
- [x] Hides stock info when showInventory is false

#### Permission-Based Tests (RBAC)
- [x] Admin: Shows edit, stock, availability buttons
- [x] Manager: Shows edit, stock buttons
- [x] Waiter: Shows quick add button only
- [x] Chef: Shows availability toggle only
- [x] Cashier: Shows no action buttons

#### Interaction Tests
- [x] Emits 'select' on card click
- [x] Emits 'add-to-order' on quick add
- [x] Emits 'edit' on edit click
- [x] Emits 'adjust-stock' on stock click
- [x] Emits 'toggle-availability' on availability toggle

---

### 6. CustomerCard.vue
**Test File**: `tests/Unit/Vue/CustomerCard.spec.js`

#### Rendering Tests
- [x] Displays customer name
- [x] Shows avatar with initials
- [x] Displays tier badge (Bronze/Silver/Gold/Platinum)
- [x] Shows correct tier color
- [x] Displays phone number
- [x] Displays email
- [x] Shows membership number
- [x] Displays loyalty points
- [x] Shows total orders count
- [x] Shows total spent amount
- [x] Displays last order date

#### Permission-Based Tests (RBAC)
- [x] Admin: Shows all info and edit button
- [x] Manager: Shows all info and edit button
- [x] Waiter: Shows basic info only
- [x] Chef: Shows no customer cards
- [x] Cashier: Shows contact info and loyalty

#### Compact Mode Tests
- [x] Hides contact details in compact mode
- [x] Hides order history in compact mode
- [x] Shows only name and tier badge
- [x] Maintains click functionality

#### Interaction Tests
- [x] Emits 'click' with customer data
- [x] Emits 'edit' on edit click
- [x] Emits 'create-order' on order button
- [x] Click-to-call works
- [x] Click-to-email works

---

## RBAC Permission Tests

### Permission Helper Tests
**Test File**: `tests/Unit/Vue/usePermissions.spec.js`

- [x] Returns true for admin on all permissions
- [x] Returns true for valid permission
- [x] Returns false for invalid permission
- [x] canAny returns true if any permission matches
- [x] canAny returns false if no permissions match
- [x] canAll returns true if all permissions match
- [x] canAll returns false if any permission missing
- [x] Handles null user gracefully
- [x] Handles undefined permissions array

### Role-Based Access Tests

#### Admin Role
- [x] Can access all components
- [x] Can perform all actions
- [x] Sees all buttons and controls

#### Manager Role
- [x] Can view all business objects
- [x] Can edit orders, products, customers
- [x] Can manage tables and assignments
- [x] Cannot access admin-only features

#### Waiter Role
- [x] Can view orders and create new ones
- [x] Can view products and add to orders
- [x] Can view basic customer info
- [x] Cannot edit products or view inventory
- [x] Cannot process payments

#### Chef Role
- [x] Can view orders and update status
- [x] Can view products and toggle availability
- [x] Cannot view customer details
- [x] Cannot edit orders or process payments

#### Cashier Role
- [x] Can view orders for payment
- [x] Can view customers for lookup
- [x] Can process payments
- [x] Cannot edit orders or products
- [x] Cannot view kitchen operations

---

## Integration Tests

### Component Integration
- [x] OrderCard uses StatusBadge correctly
- [x] OrderCard uses AmountDisplay correctly
- [x] OrderItemDisplay uses StatusBadge correctly
- [x] ProductCard uses AmountDisplay correctly
- [x] CustomerCard uses AmountDisplay correctly

### Floor Management Integration
- [x] TableDetails uses OrderCard
- [x] TableDetails uses OrderItemDisplay
- [x] OrderAssignmentModal uses OrderCard
- [x] Components respect floor management permissions

---

## Visual Regression Tests

### Responsive Design
- [x] Components render correctly on desktop (>1024px)
- [x] Components render correctly on tablet (768-1024px)
- [x] Components render correctly on mobile (<768px)
- [x] Compact modes activate on small screens

### Theme Consistency
- [x] All status colors match design system
- [x] All buttons use consistent styling
- [x] Typography is consistent across components
- [x] Spacing follows 8px grid system

---

## Performance Tests

### Rendering Performance
- [x] Component renders in <50ms
- [x] List of 100 items renders in <500ms
- [x] No unnecessary re-renders
- [x] Computed properties are cached

### Bundle Size
- [x] Components are tree-shakeable
- [x] No duplicate code across components
- [x] Shared utilities extracted

---

## Accessibility Tests

### A11y Requirements
- [x] All interactive elements are keyboard accessible
- [x] Color contrast meets WCAG 2.1 AA
- [x] Status indicators have text alternatives
- [x] Focus states are visible
- [x] Screen reader labels present

---

## Test Coverage Requirements

- **Unit Tests**: 90%+ coverage for each component
- **Integration Tests**: 80%+ coverage
- **RBAC Tests**: 100% of permission scenarios
- **Edge Cases**: All documented edge cases tested

---

## Success Criteria Summary

✅ **All checklist items must pass before deployment**

### Minimum Requirements:
1. All 6 base components implemented
2. All rendering tests passing
3. All RBAC permission tests passing
4. All interaction tests passing
5. 90%+ test coverage achieved
6. No console errors or warnings
7. Responsive design verified
8. Accessibility standards met
