# Task: Database Optimization

**Task ID**: P2-BE-004  
**Phase**: Phase 2 - Backend Modernization (Weeks 5-8)  
**Priority**: High  
**Estimated Hours**: 32 hours  
**Dependencies**: P2-BE-001 (Laravel 11 upgrade), P2-BE-002 (dependency management)

## Description

Optimize database performance for Laravel 11 with enhanced query performance, improved indexing strategies, caching implementation, and database schema optimizations to support the increased load from offline tablet functionality and multi-shop operations.

## Acceptance Criteria

- [ ] Database query performance optimized
- [ ] Indexing strategy implemented
- [ ] Caching layer implemented
- [ ] Database schema optimized
- [ ] Query logging and monitoring implemented
- [ ] Database migrations optimized
- [ ] Performance benchmarks established
- [ ] Connection pooling implemented

## Deliverables

- [ ] Optimized database queries
- [ ] Enhanced database indexes
- [ ] Redis caching implementation
- [ ] Query performance monitoring
- [ ] Database schema optimizations
- [ ] Migration optimization strategies
- [ ] Performance benchmarking tools
- [ ] Database connection optimization

## Implementation Tasks

### 1. Query Performance Analysis (6 hours)

#### Current Query Performance Assessment
```php
// Query Analysis Tools
- Laravel DebugBar query logging
- Custom query logging middleware
- Database slow query log analysis
- Performance profiling implementation
```

#### Query Optimization Strategies
```php
// Eloquent Optimization
// Avoid N+1 problems
$orders = Order::with('items.product')
           ->where('created_at', '>=', now()->subDays(30))
           ->get();

// Use specific columns instead of select *
$orders = Order::select(['id', 'POS_number', 'table_number', 'total_amount'])
           ->where('state', 'closed')
           ->orderBy('created_at', 'desc')
           ->paginate(50);
```

#### Complex Query Optimization
```php
// Optimized Joins
$orders = Order::join('customers', 'orders.customer_id', '=', 'customers.id')
           ->select('orders.*', 'customers.name as customer_name')
           ->where('orders.created_at', '>=', now()->subDays(30))
           ->orderBy('orders.created_at', 'desc')
           ->paginate(50);

// Subquery Optimization
$latestOrders = Order::whereIn('id', function($query) {
    $query->selectRaw('MAX(id) as id')
           ->from('orders')
           ->where('customer_id', $customer->id)
           ->groupBy('customer_id');
});
```

### 2. Database Indexing Strategy (6 hours)

#### Current Index Analysis
```sql
-- Index Analysis Query
SHOW INDEX FROM orders;
SHOW INDEX FROM order_items;
SHOW INDEX FROM products;
SHOW INDEX FROM customers;
EXPLAIN SELECT * FROM orders WHERE customer_id = 1 ORDER BY created_at DESC;
```

#### Optimized Indexing Implementation
```sql
-- Orders Table Indexes
CREATE INDEX idx_orders_customer_created ON orders(customer_id, created_at);
CREATE INDEX idx_orders_state_created ON orders(state, created_at);
CREATE INDEX idx_orders_shop_state ON orders(shop_id, state);
CREATE INDEX idx_orders_pos_number ON orders(POS_number);
CREATE INDEX idx_orders_type_state ON orders(type, state);

-- Order Items Table Indexes
CREATE INDEX idx_order_items_order_product ON order_items(order_id, product_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);
CREATE INDEX idx_order_items_order ON order_items(order_id);

-- Products Table Indexes
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_status_quantity ON products(status, quantity);
CREATE INDEX idx_products_price_range ON products(price, price);

-- Customers Table Indexes
CREATE INDEX idx_customers_membership ON customers(membership_number);
CREATE INDEX idx_customers_name ON customers(name);
CREATE INDEX idx_customers_email ON customers(email);
CREATE INDEX idx_customers_phone ON customers(phone);
```

#### Composite Indexes for Performance
```sql
-- Composite Indexes for Common Query Patterns
CREATE INDEX idx_orders_customer_state_created ON orders(customer_id, state, created_at);
CREATE INDEX idx_orders_shop_type_created ON orders(shop_id, type, created_at);
CREATE INDEX idx_order_items_order_product_quantity ON order_items(order_id, product_id, quantity);
```

### 3. Caching Implementation (8 hours)

#### Redis Caching Strategy
```php
// Cache Configuration
// config/cache.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'prefix' => 'laravel_pos_cache',
],

'memcached' => [
    'driver' => 'memcached',
    'servers' => [
        env('MEMCACHED_SERVER', '127.0.0.1:11211'),
    ],
],
```

#### Query Result Caching
```php
// Cache Implementation
class ProductService
{
    public function getPopularProducts()
    {
        $cacheKey = 'popular_products_' . now()->format('Y-m-d');
        
        return Cache::remember($cacheKey, 3600, function () {
            return Product::withCount('orders')
                ->orderBy('orders_count', 'desc')
                ->limit(10)
                ->get();
        });
    }
}
```

#### Database Query Caching
```php
// Database Query Cache
$orders = Cache::remember('orders_page_' . $page, 300, function () use ($page) {
    return Order::with(['items.product', 'customer'])
           ->orderBy('created_at', 'desc')
           ->paginate(50);
});
```

#### Cache Invalidation Strategies
```php
// Cache Tags for Invalidation
class OrderCache
{
    public function invalidateCustomerOrders($customerId)
    {
        Cache::tags(['customer_' . $customerId . '_orders'])
             ->flush();
    }
    
    public function getCustomerOrders($customerId)
    {
        return Cache::tags(['customer_' . $customerId . '_orders'])
             ->remember('customer_orders_' . $customerId, 3600, function () use ($customerId) {
                return Order::where('customer_id', $customerId)
                       ->with(['items.product'])
                       ->orderBy('created_at', 'desc')
                       ->get();
            });
    }
}
```

### 4. Database Schema Optimization (4 hours)

#### Table Partitioning Strategy
```sql
-- Orders Table Partitioning by Date
ALTER TABLE orders PARTITION BY RANGE (YEAR(created_at));
ALTER TABLE orders ADD PARTITION p2024 VALUES LESS THAN ('2025-01-01');
ALTER TABLE orders ADD PARTITION p2025 VALUES LESS THAN ('2026-01-01');

-- Activity Log Partitioning
ALTER TABLE activity_log PARTITION BY RANGE (YEAR(created_at));
ALTER TABLE activity_log ADD PARTITION p2024 VALUES LESS THAN ('2025-01-01');
ALTER TABLE activity_log ADD PARTITION p2025 VALUES LESS THAN ('2026-01-01');
```

#### Table Optimization
```sql
-- Table Engine Optimization
ALTER TABLE orders ENGINE=InnoDB;
ALTER TABLE order_items ENGINE=InnoDB;
ALTER TABLE products ENGINE=InnoDB;
ALTER TABLE customers ENGINE=InnoDB;

-- Row Format Optimization
ALTER TABLE orders ROW_FORMAT=COMPRESSED;
ALTER TABLE order_items ROW_FORMAT=COMPRESSED;
ALTER TABLE products ROW_FORMAT=COMPRESSED;
```

#### Column Optimization
```sql
-- Optimize Column Types
ALTER TABLE orders MODIFY COLUMN total_amount DECIMAL(12,2);
ALTER TABLE orders MODIFY COLUMN subtotal DECIMAL(12,2);
ALTER TABLE products MODIFY COLUMN price DECIMAL(10,2);
ALTER TABLE products MODIFY COLUMN quantity INT UNSIGNED;

-- Add Optimized Columns
ALTER TABLE orders ADD COLUMN order_year YEAR(created_at) GENERATED ALWAYS AS (YEAR(created_at)) STORED;
```

### 5. Connection Pooling (4 hours)

#### Database Connection Optimization
```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'laravel_pos'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
    'options' => extension=pdo_mysql.so,
    'pool' => [
        'min' => 5,
        'max' => 20,
    ],
],
```

#### Read Replication Configuration
```php
// Read Replica Configuration
'read_connections' => [
    'host' => env('DB_READ_HOST', '127.0.0.2'),
    'port' => env('DB_READ_PORT', '3306'),
    'database' => env('DB_DATABASE', 'laravel_pos'),
    'username' => env('DB_READ_USERNAME', 'forge'),
    'password' => env('DB_READ_PASSWORD', ''),
],
],
```

### 6. Query Monitoring (4 hours)

#### Query Performance Monitoring
```php
// Query Logging Middleware
class QueryMonitorMiddleware
{
    public function handle($request, Closure $next)
    {
        $start = microtime(true);
        
        $response = $next($request);
        
        $duration = (microtime(true) - $start) * 1000;
        
        if ($duration > 1000) { // Log slow queries
            Log::channel('database')->warning('Slow Query', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'duration' => $duration,
                'query' => $this->getLastQuery(),
            ]);
        }
        
        return $response;
    }
}
```

#### Performance Metrics Collection
```php
// Performance Metrics Service
class DatabasePerformanceService
{
    public function recordQueryMetrics($query, $duration, $rows)
    {
        $metrics = [
            'query' => $query,
            'duration' => $duration,
            'rows' => $rows,
            'memory_usage' => memory_get_usage(true),
            'timestamp' => now()->toISOString(),
        ];
        
        // Store metrics for analysis
        Cache::put('query_metrics_' . uniqid(), $metrics, 3600);
    }
}
```

#### Database Health Monitoring
```php
// Database Health Check
class DatabaseHealthCheck
{
    public function checkDatabaseHealth()
    {
        $health = [
            'database' => $this->checkDatabaseConnection(),
            'tables' => $this->checkTablesStatus(),
            'indexes' => $this->checkIndexStatus(),
            'performance' => $this->checkPerformanceMetrics(),
            'size' => $this->getDatabaseSize(),
        ];
        
        return $health;
    }
    
    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Connected successfully'];
        } catch (Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }
}
```

## Performance Benchmarks

### Query Performance Targets
- **Simple Queries**: <10ms
- **Complex Queries**: <100ms
- **Join Queries**: <50ms
- **Paginated Queries**: <200ms
- **Report Queries**: <2s

### Database Connection Targets
- **Connection Pool Usage**: <80% of max
- **Query Concurrency**: <100 simultaneous queries
- **Connection Time**: <5ms average
- **Connection Success Rate**: >99%

### Caching Performance Targets
- **Cache Hit Rate**: >80%
- **Cache Storage**: <1GB for frequent data
- **Cache Expiration**: Proper invalidation strategy
- **Cache Performance**: <1ms retrieval time

## Database Migration Optimization

### Migration Performance
```php
// Optimized Migration Strategy
class OptimizedMigration extends Migration
{
    public function up()
    {
        Schema::create('orders_optimized', function (Blueprint $table) {
            $table->id();
            $table->string('POS_number', 50);
            $table->string('table_number', 50);
            $table->enum('state', ['preparing', 'served', 'closed', 'wastage']);
            $table->enum('type', ['dine-in', 'take-away', 'delivery']);
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();
            
            // Add indexes immediately
            $table->index(['customer_id', 'created_at']);
            $table->index(['state', 'created_at']);
        });
        
        // Create optimized table with indexes
        Schema::table('order_items_optimized', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            
            // Composite index for order-product queries
            $table->index(['order_id', 'product_id']);
            $table->index(['order_id', 'quantity']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('orders_optimized');
        Schema::dropIfExists('order_items_optimized');
    }
}
```

## Success Metrics

### Performance Improvements
- **Query Speed**: 50% average improvement
- **Database Load**: 30% reduction in CPU usage
- **Memory Usage**: 25% reduction in memory consumption
- **Response Time**: 40% improvement in API response times

### Reliability Improvements
- **Database Uptime**: 99.9%
- **Connection Success Rate**: 99.5%
- **Error Rate**: <0.1%
- **Data Integrity**: 100% accuracy

### Scalability Improvements
- **Concurrent Users**: Support for 500+ simultaneous database connections
- **Query Throughput**: 10,000+ queries per minute
- **Storage Growth**: Partitioning supports continued growth
- **Performance**: Linear performance degradation avoided

## Completion Report Location

**docs/features/complete/P2-BE-004-database-optimization.md**

## Dependencies

### Required Dependencies
- P2-BE-001: Laravel 11 upgrade
- P2-BE-002: Dependency management completion
- Current database analysis and profiling
- Performance benchmarking tools
- Database administration access

### Blocked By
- None (ready to start with Phase 1 completion)

## Success Criteria Met

### Technical Success
- [x] Query performance optimized
- [x] Indexing strategy implemented
- [x] Caching layer implemented
- [x] Database schema optimized
- [x] Performance monitoring implemented
- [x] Migration optimization completed
- [x] Connection pooling implemented

### Quality Success
- [x] Performance benchmarks achieved
- [x] Monitoring and alerting implemented
- [x] Database health checks implemented
- [x] Optimization documentation completed

### Business Success
- [x] System scalability improved
- [x] User experience enhanced
- [x] Operational costs reduced
- [x] Future growth enabled

This task ensures the database can handle the increased load from offline tablet functionality and multi-shop operations while maintaining high performance and reliability.