# Vue-Based Order Edit Interface

## Overview

This document describes the new Vue.js-based order edit interface that improves upon the existing Livewire-based implementation. The new interface provides better user experience, real-time updates, and cleaner code architecture while maintaining all existing functionality.

## Architecture

### Components Structure
```
resources/js/components/orders/
├── OrderEdit.vue           # Main order edit component
└── index.js                # Component exports

resources/views/orders/vue/
└── edit.blade.php          # Blade wrapper for Vue component

resources/js/
└── order-edit.js           # Entry point for Vue app
```

### Key Improvements Over Livewire Version

1. **Better State Management**
   - Centralized reactive state with Vue 3 Composition API
   - No more Livewire wire:loading flickering
   - Smoother UI transitions

2. **Enhanced User Experience**
   - Real-time customer search with debouncing
   - Collapsible sections for better space management
   - Improved mobile responsiveness
   - Better visual feedback with StatusBadge and AmountDisplay components

3. **Cleaner Code Architecture**
   - Separation of concerns with dedicated business components
   - Reusable OrderItemDisplay component
   - Type-safe props and events
   - Better RBAC implementation

4. **Performance**
   - Reduced server round-trips
   - Client-side filtering and sorting
   - Optimized re-rendering

## Features

### Order Data Section (Collapsible)
- Shop selection (dropdown)
- Order type (dine-in/take-away/delivery)
- Table number (conditional on dine-in)
- Waiter name (conditional on dine-in)
- Customer search with autocomplete
- Order notes

**RBAC**: Visible to Admin, Manager, Waiter

### Order Items List
- Display all order items with:
  - Product name
  - Unit price
  - Quantity (with +/- controls)
  - Total price
  - Status badge
  - Delete button
  - Notes support
- Uses OrderItemDisplay component
- Real-time total calculation

**RBAC**: 
- Quantity edit: Admin, Manager, Waiter
- Delete: Admin, Manager, Waiter
- Status update: Chef, Admin

### Product Selection Panel
- Category accordion interface
- Product cards with:
  - Name and price
  - Stock indicator (low stock/out of stock)
  - One-click add to order
- Search functionality
- Responsive grid layout

### Discounts & Charges
- Checkbox list of available discounts
- Visual distinction between discounts (green) and charges (yellow)
- Real-time calculation of discount/charge amounts

**RBAC**: Admin, Manager, Cashier only

### Order Totals
- Subtotal (sum of all items)
- Discount amount (if applicable)
- Charges amount (if applicable)
- Net payable (final amount)

### Action Buttons
- Print order
- Process payment (when order is served)
- Cancel order

**RBAC**: 
- Print: All users
- Payment: Admin, Manager, Cashier
- Cancel: Admin, Manager

## Props

### OrderEdit Component

```javascript
{
  order: {
    id: Number,
    POS_number: String,
    state: String,
    type: String,
    table_number: String,
    waiter_name: String,
    notes: String,
    total_amount: Number,
    items: Array,
    customer: Object,
    discounts: Array,
    shop_id: Number
  },
  user: {
    id: Number,
    type: String, // 'admin', 'manager', 'waiter', 'chef', 'cashier'
    first_name: String,
    last_name: String
  },
  userShops: Array,
  categories: Array,
  discounts: Array,
  customers: Array
}
```

## Events

### Emitted Events

1. `@update-order` - Order data updated
   ```javascript
   { id, shop_id, type, table_number, waiter_name, notes, customer_id }
   ```

2. `@add-item` - New item added to order
   ```javascript
   { product_id, product_name, unit_price, quantity, is_misc }
   ```

3. `@update-item` - Item quantity/status updated
   ```javascript
   { itemId, quantity, status, notes }
   ```

4. `@delete-item` - Item removed from order
   ```javascript
   itemId
   ```

5. `@toggle-discount` - Discount applied/removed
   ```javascript
   discountId
   ```

6. `@process-payment` - Payment initiated
   ```javascript
   orderId
   ```

7. `@cancel-order` - Order cancellation
   ```javascript
   orderId
   ```

8. `@print-order` - Print order requested
   ```javascript
   orderId
   ```

## API Integration

The Vue component communicates with the backend through the REST API endpoints:

- `PUT /api/v1/orders/{id}` - Update order data
- `POST /api/v1/orders/{id}/items` - Add item
- `PUT /api/v1/orders/{id}/items/{itemId}` - Update item
- `DELETE /api/v1/orders/{id}/items/{itemId}` - Delete item
- `DELETE /api/v1/orders/{id}` - Cancel order

## Usage

### In Blade Template

```blade
<div id="order-edit-app">
    <order-edit
        :order="{{ json_encode($order) }}"
        :user="{{ json_encode(auth()->user()) }}"
        :user-shops="{{ json_encode(auth()->user()->shops) }}"
        :categories="{{ json_encode($categories) }}"
        :discounts="{{ json_encode($discounts) }}"
        :customers="{{ json_encode($customers) }}"
        @update-order="handleOrderUpdate"
        @add-item="handleAddItem"
        @update-item="handleUpdateItem"
        @delete-item="handleDeleteItem"
        @toggle-discount="handleToggleDiscount"
        @process-payment="handleProcessPayment"
        @cancel-order="handleCancelOrder"
        @print-order="handlePrintOrder"
    />
</div>
```

### Route Setup

Add to `routes/web.php`:

```php
Route::get('/orders/{order}/vue-edit', [OrderController::class, 'vueEdit'])
    ->name('orders.vue-edit')
    ->middleware('auth');
```

### Controller Method

```php
public function vueEdit(Order $order)
{
    $this->authorize('update', $order);
    
    $categories = Category::with(['products' => function($query) {
        $query->where('is_available', true);
    }])->get();
    
    $discounts = Discount::all();
    $customers = Customer::select('id', 'name', 'membership_number')->get();
    
    return view('orders.vue.edit', compact('order', 'categories', 'discounts', 'customers'));
}
```

## Webpack/Vite Configuration

Add to `vite.config.js` or `webpack.mix.js`:

```javascript
// vite.config.js
export default defineConfig({
    // ... other config
    build: {
        rollupOptions: {
            input: {
                'order-edit': 'resources/js/order-edit.js',
                // ... other entries
            }
        }
    }
});
```

## Testing

### Component Tests
- Order data form validation
- Item quantity updates
- Customer search functionality
- Product filtering
- Discount calculations
- RBAC permission checks

### E2E Tests
- Complete order edit workflow
- Add/remove items
- Apply discounts
- Process payment
- Cancel order

## Migration Guide

### From Livewire to Vue

1. **Keep existing Livewire components** for backward compatibility
2. **Add new Vue route** alongside existing edit route
3. **Gradual rollout** - test with select users first
4. **Feature parity** - ensure all features work identically
5. **Performance comparison** - monitor load times and interactions

### Rollback Plan

If issues arise:
1. Simply redirect users to existing Livewire route
2. No database changes required
3. Both implementations can coexist

## Future Enhancements

1. **Offline Support** - Cache order data for offline editing
2. **Real-time Collaboration** - WebSocket updates when multiple users edit
3. **Keyboard Shortcuts** - Power user features
4. **Drag & Drop** - Reorder items in the list
5. **Split Bills** - Divide order among multiple payments
6. **Order Templates** - Save common order configurations

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Accessibility

- ARIA labels on all interactive elements
- Keyboard navigation support
- High contrast mode compatible
- Screen reader friendly
- Focus indicators visible

---

**Note**: This Vue implementation is designed to be a drop-in replacement for the existing Livewire-based order edit page. Both can coexist during the transition period.