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
[x] Bill Generation & Printing
[ ] Payment Handling
[x] Discounts & Rebates

[ ] Cashier Flow:

[x] Handle Customer orders of Products and Qty
[ ] Assign the Dishes from the Placed orders to Kitchen
[ ] Serve the Prepared Dishes from orders to Customers on their Table, via a Waiter/ Delivery boy
[x] Give Discounts
[ ] Register Tips if any
[ ] Take Payments

[ ] Chef Flow:

[ ] View Dishes from Placed Orders
[ ] Verify Recipe for the Dish
[ ] Mark as Prepared
[ ] Register Wastages

#### Models

1. Product [ ]

    1. Name
    2. Description (nullable)
    3. Image (nullable)
    4. Price
    5. Quantity (default: 1000)
    6. Availability Status (default: true)
    7. Product_Shop [hasMany] (Pivot)
    8. Category_Products [hasMany] (Pivot)
    9. Discount_Products [hasMany] (Pivot)

2. Order [ ]
    1. POS_number
    2. Table : string
    3. Waiter / Delivery Boy: User
    4. State : 'preparing', 'served', 'closed', 'wastage'
    5. Type: 'dine-in', 'take-away', 'delivery'
    6. Customer [foreign]
    7. User [foreign]
    8. Shop [foreign]
    9. Order_Item : (hasMany) [foreign] (pivot)
       a. Price // Amount
       b. Quantity
    10. Discount_Orders (hasMany) [foreign] (pivot)
    11. Payments (hasMany)

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

### Requirements 27 May

-   searchable members in orders edit
-   discount controller i/o update/ test
