<p align="center">
    <h1 align="center">Laravel Restaurant</h1>
</p>

## Modules

1. POS
   a. Kitchen
2. Accounting
3. Inventory

### POS Features

[ ] Menu : Display products grouped by their Categories and Cuisines
[ ] Order Handling via Waiters and Tables
[ ] Order Status via Kitchen
[ ] Bill Generation & Printing
[ ] Payment Handling
[ ] Discounts & Rebates

[ ] Cashier Flow:

a. Handle Customer orders of Products and Qty
b. Assign the Dishes from the Placed orders to Kitchen
c. Serve the Prepared Dishes from orders to Customers on their Table, via a Waiter/ Delivery boy
d. Give Discounts
e. Register Tips if any
f. Take Payments

[ ] Chef Flow:

a. View Dishes from Placed Orders
b. Verify Recipe for the Dish
c. Mark as Prepared
d. Register Wastages

#### Models

1. Product [ ]

    1. Name
    2. Price
    3. Cuisines (ManytoMany) [ ]
    4. Category (belongsTo) [ ]

2. Order [ ]
    1. Table : string
    2. Waiter / Delivery Boy: User
    3. Tip Amount
    4. Customer
    5. Status : PLACED, PREPARED, SERVED, REJECTED
    6. Items : Products (hasMany), Qty
    7. Discount (hasMany)
    8. Payments (hasMany)

### Accounting Features

[ ] Sales Reports
[ ] Expenses Records & Reports
[ ] Holdings Records & Stock Reports
[ ] Profit & Loss Reports

### Inventory Features

[ ] Stock Management
a. Items in Qty (Qty as No or Wt based on Item)
b. Stock Audit 1. Print Stock Correction Form 2. Confirm Stock Correction 3. Record Wastages
c. Stock Wastages Record
[ ] Transactions:
a. Type : UP, DOWN
b. By User
c. Items & Qty
d. Item Rates if any
[ ] Inventory Reports
