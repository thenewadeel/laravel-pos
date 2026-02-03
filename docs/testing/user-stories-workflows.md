# User Stories & Workflows for Manual Testing

## Test Users

| Role | Email | Password | Permissions |
|------|-------|----------|-------------|
| **Admin/Manager** | admin@wt.pos | admin123 | Full system access |
| **Manager** | manager@wt.pos | manager123 | Manage floors, tables, orders, reports |
| **Cashier** | cashier@wt.pos | cashier123 | Process payments, view orders |
| **Accountant** | accountant@wt.pos | accountant123 | View reports, expenses |
| **Chef** | chef@wt.pos | chef123 | Update order status, view orders |
| **Stock Boy** | stock@wt.pos | stock123 | Manage inventory |
| **Waiter** | waiter@wt.pos | waiter123 | Create orders, manage tables |

---

## User Stories by Role

### 1. Manager/Admin User Stories

**US-M1: View Floor Layout**
- As a manager, I want to see all floors and tables
- So that I can monitor restaurant occupancy
- **Acceptance**: Can see floor layout with color-coded tables (green=available, red=occupied, yellow=reserved)

**US-M2: Add New Floor**
- As a manager, I want to add a new floor
- So that I can expand restaurant capacity
- **Acceptance**: Click "Add New Floor", enter floor name, floor appears in layout

**US-M3: Add Table to Floor**
- As a manager, I want to add tables to a floor
- So that I can configure seating arrangements
- **Acceptance**: Click "Add Table", enter table number and capacity, table appears on floor

**US-M4: Delete Floor/Table**
- As a manager, I want to remove floors or tables
- So that I can reconfigure the restaurant layout
- **Acceptance**: Click delete icon, confirm deletion, item removed

**US-M5: View Table Details**
- As a manager, I want to click on a table and see its current order
- So that I can monitor table status
- **Acceptance**: Click table, side panel opens showing order details and actions

**US-M6: Create Order from Floor View**
- As a manager, I want to create a new order for an empty table
- So that I can start serving customers
- **Acceptance**: Click empty table, click "Create Order", order created

**US-M7: Process Payment**
- As a manager, I want to process payment for an occupied table
- So that I can complete transactions
- **Acceptance**: Click occupied table, click "Pay", payment processed

**US-M8: View Daily Stats**
- As a manager, I want to see today's sales and order count
- So that I can track business performance
- **Acceptance**: Stats bar shows available/occupied/reserved tables and total sales

---

### 2. Cashier User Stories

**US-C1: Process Payment**
- As a cashier, I want to process payments for orders
- So that I can complete customer transactions
- **Acceptance**: Can click occupied table, see "Pay" button, process payment

**US-C2: View Order Details**
- As a cashier, I want to view order details before payment
- So that I can verify the bill
- **Acceptance**: Can see order items, quantities, and total before processing

**US-C3: View Floor Status**
- As a cashier, I want to see which tables are occupied
- So that I can anticipate payments
- **Acceptance**: Floor view shows occupied tables with red color

---

### 3. Waiter User Stories

**US-W1: Create New Order**
- As a waiter, I want to create orders for customers
- So that I can start serving them
- **Acceptance**: Can select table, create order, add items

**US-W2: Add Items to Order**
- As a waiter, I want to add food/drink items to an existing order
- So that I can update the customer's order
- **Acceptance**: Can open order, add products from categories

**US-W3: Update Item Quantity**
- As a waiter, I want to change the quantity of items
- So that I can correct orders
- **Acceptance**: Can increase/decrease item quantities

**US-W4: Delete Items**
- As a waiter, I want to remove items from an order
- So that I can fix mistakes
- **Acceptance**: Can delete items with confirmation

**US-W5: View Order Status**
- As a waiter, I want to see if items are ready
- So that I can serve customers promptly
- **Acceptance**: Can see item status (preparing, ready, served)

---

### 4. Chef User Stories

**US-CH1: View Orders**
- As a chef, I want to see all active orders
- So that I can prepare food
- **Acceptance**: Can view orders and their items

**US-CH2: Update Item Status**
- As a chef, I want to mark items as ready
- So that waiters know to pick them up
- **Acceptance**: Can click "Mark Ready" on items

---

### 5. Stock Boy User Stories

**US-SB1: View Inventory**
- As a stock boy, I want to see current stock levels
- So that I can monitor inventory
- **Acceptance**: Can view products with quantities

**US-SB2: Update Stock**
- As a stock boy, I want to update product quantities
- So that I can record new stock
- **Acceptance**: Can adjust stock levels for products

**US-SB3: Low Stock Alerts**
- As a stock boy, I want to see which products are low
- So that I can reorder
- **Acceptance**: Products with low stock shown with warning

---

## Workflow Scenarios

### Scenario 1: New Customer Dine-in

**Actors**: Waiter, Chef, Cashier

**Steps**:
1. Waiter opens floor view
2. Waiter selects available table (green)
3. Waiter clicks "Create Order"
4. Waiter selects "Dine-in" type
5. Waiter adds items from categories
6. Order appears in kitchen
7. Chef prepares items
8. Chef marks items as "Ready"
9. Waiter serves food
10. Customer finishes meal
11. Cashier processes payment
12. Table becomes available

**Expected Result**: Order completed, payment processed, table freed

---

### Scenario 2: Manager Adds New Floor

**Actors**: Manager

**Steps**:
1. Manager opens floor-restaurant view
2. Manager clicks "Add New Floor"
3. Manager enters floor name (e.g., "Second Floor")
4. Floor appears in layout
5. Manager clicks "Add Table" on new floor
6. Manager enters table number and capacity
7. Table appears on floor

**Expected Result**: New floor with tables visible in layout

---

### Scenario 3: Cashier Processes Payment

**Actors**: Cashier

**Steps**:
1. Cashier opens floor view
2. Cashier sees occupied table (red)
3. Cashier clicks on table
4. Side panel shows order details
5. Cashier reviews order items and total
6. Cashier clicks "Pay" button
7. Payment processed
8. Table becomes available

**Expected Result**: Payment completed, order closed

---

### Scenario 4: Chef Marks Items Ready

**Actors**: Chef

**Steps**:
1. Chef views active orders
2. Chef sees new order with items
3. Chef prepares food
4. Chef clicks "Mark Ready" on prepared items
5. Waiter sees items are ready
6. Waiter picks up food

**Expected Result**: Items marked ready, waiter notified

---

### Scenario 5: Stock Boy Updates Inventory

**Actors**: Stock Boy

**Steps**:
1. Stock boy views product list
2. Stock boy sees low stock warning on some items
3. Stock boy clicks on product
4. Stock boy updates quantity
5. Low stock warning disappears

**Expected Result**: Stock levels updated

---

## Testing Checklist

### Pre-Test Setup
- [ ] Run `php artisan db:seed --class=UserSeeder`
- [ ] Verify all 7 test users created
- [ ] Clear browser cache
- [ ] Start dev server: `npm run dev`

### Login Tests
- [ ] Login as admin@wt.pos
- [ ] Login as manager@wt.pos
- [ ] Login as cashier@wt.pos
- [ ] Login as accountant@wt.pos
- [ ] Login as chef@wt.pos
- [ ] Login as stock@wt.pos
- [ ] Login as waiter@wt.pos

### Floor-Restaurant View Tests (Manager)
- [ ] View floor layout
- [ ] See stats bar with correct numbers
- [ ] Click on table to see details
- [ ] Add new floor
- [ ] Add table to floor
- [ ] Create order from empty table
- [ ] Process payment from occupied table
- [ ] Delete floor/table

### Order Management Tests (Waiter)
- [ ] Create new order
- [ ] Add items to order
- [ ] Update item quantities
- [ ] Delete items from order
- [ ] View order status

### Payment Tests (Cashier)
- [ ] View occupied tables
- [ ] Click table to see order
- [ ] Process payment
- [ ] Verify table becomes available

### Kitchen Tests (Chef)
- [ ] View active orders
- [ ] Mark items as ready
- [ ] View order status updates

### Inventory Tests (Stock)
- [ ] View product list
- [ ] See low stock warnings
- [ ] Update stock quantities

---

## Feedback Template

When testing, please provide feedback in this format:

```
Feature: [Feature Name]
User: [Email used]
Status: [PASS/FAIL/PARTIAL]
Issues: [Any issues encountered]
Notes: [Additional observations]
```

Example:
```
Feature: Add New Floor
User: manager@wt.pos
Status: PASS
Issues: None
Notes: Floor added successfully, appeared immediately in layout
```
