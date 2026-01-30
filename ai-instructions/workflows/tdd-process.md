# Test-Driven Development (TDD) Workflow

## Overview

This document outlines the TDD workflow for Laravel POS 2026 upgrade project, following Red-Green-Refactor cycle.

## TDD Process

### 1. RED Phase - Write Failing Test

**Objective**: Write a test that clearly defines the behavior you want to implement.

#### Steps:
1. **Understand Requirements**
   - Read task specifications in `docs/features/plans/`
   - Identify specific behavior to implement
   - Define acceptance criteria

2. **Write Test**
   - Create test file if doesn't exist
   - Write test that clearly describes expected behavior
   - Ensure test fails with meaningful error

3. **Verify Failure**
   - Run `./scripts/test-runner.sh`
   - Confirm test fails with clear error message
   - Check that test is testing the right thing

#### Example Order Creation Test:
```php
// tests/Feature/OrderCreationTest.php
public function test_waiter_can_create_order_offline()
{
    // RED: This test should fail initially
    $tabletDevice = TabletDevice::factory()->create();
    $orderData = [
        'table_number' => 'Table 1',
        'waiter_name' => 'John Waiter',
        'items' => [
            ['product_id' => 1, 'quantity' => 2],
            ['product_id' => 2, 'quantity' => 1],
        ],
        'customer_id' => null,
        'type' => 'dine-in',
    ];
    
    // This should fail because offline functionality doesn't exist yet
    $response = $this->postJson('/api/orders/offline', $orderData);
    
    $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'POS_number', 
                'table_number',
                'waiter_name',
                'items',
                'total_amount'
            ]);
}
```

### 2. GREEN Phase - Make Test Pass

**Objective**: Write minimal code to make the failing test pass.

#### Steps:
1. **Implement Minimum Code**
   - Write just enough code to pass the test
   - Don't over-engineer
   - Focus on making test pass

2. **Run Tests**
   - Execute `./scripts/test-runner.sh`
   - Confirm new test passes
   - Ensure no regressions

3. **No More, No Less**
   - Stop when test passes
   - Don't add extra features
   - Keep code simple

#### Example Minimal Implementation:
```php
// routes/api.php
Route::post('/orders/offline', [OrderController::class, 'storeOffline']);

// app/Http/Controllers/OrderController.php
public function storeOffline(Request $request)
{
    // GREEN: Minimal implementation to pass test
    $order = Order::create([
        'POS_number' => 'POS-' . time(),
        'table_number' => $request->table_number,
        'waiter_name' => $request->waiter_name,
        'customer_id' => $request->customer_id,
        'type' => $request->type,
        'state' => 'pending_sync',
    ]);
    
    // Create order items (simplified)
    foreach ($request->items as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
        ]);
    }
    
    return response()->json($order->load('items'), 201);
}
```

### 3. REFACTOR Phase - Improve Code

**Objective**: Improve code design while keeping tests passing.

#### Steps:
1. **Run Full Test Suite**
   - Ensure all tests pass before refactoring
   - Confirm coverage is adequate
   - Check performance benchmarks

2. **Identify Improvements**
   - Code duplication
   - Complex logic
   - Poor naming
   - Missing abstractions

3. **Refactor Incrementally**
   - Make small changes
   - Run tests after each change
   - Keep tests green throughout

4. **Final Verification**
   - Run full test suite
   - Check performance
   - Review code quality

#### Example Refactoring:
```php
// Refactored to service class
// app/Services/OfflineOrderService.php
class OfflineOrderService
{
    public function createOrder(array $orderData): Order
    {
        DB::transaction(function () use ($orderData) {
            $order = $this->createOrderRecord($orderData);
            $this->createOrderItems($order, $orderData['items']);
            $this->queueForSync($order);
            
            return $order;
        });
    }
    
    private function createOrderRecord(array $data): Order
    {
        return Order::create([
            'POS_number' => $this->generatePOSNumber(),
            'table_number' => $data['table_number'],
            'waiter_name' => $data['waiter_name'],
            'customer_id' => $data['customer_id'],
            'type' => $data['type'],
            'state' => 'pending_sync',
        ]);
    }
    
    // ... other methods
}

// app/Http/Controllers/OrderController.php  
public function storeOffline(Request $request, OfflineOrderService $service)
{
    $order = $service->createOrder($request->validated());
    return response()->json($order->load('items'), 201);
}
```

## TDD Best Practices

### Test Writing Guidelines

#### 1. Clear Test Names
```php
// Good
public function test_offline_order_is_stored_with_pending_sync_status()

// Bad  
public function test_order()
```

#### 2. One Assertion Per Test
```php
// Good
public function test_order_creates_with_correct_pos_number()
{
    $order = Order::createValidOrder();
    $this->assertMatchesPattern($order->POS_number, '/^POS-\d+$/');
}

public function test_order_has_initial_pending_sync_status()
{
    $order = Order::createValidOrder();
    $this->assertEquals('pending_sync', $order->state);
}

// Bad
public function test_order_creation()
{
    $order = Order::createValidOrder();
    $this->assertMatchesPattern($order->POS_number, '/^POS-\d+$/');
    $this->assertEquals('pending_sync', $order->state);
    $this->assertNotNull($order->table_number);
}
```

#### 3. Use Meaningful Test Data
```php
// Good - Factory with realistic data
$order = Order::factory()->dineIn()->create();

// Bad - Vague test data
$order = Order::factory()->create();
```

### Code Implementation Guidelines

#### 1. Minimal Implementation
- Write only what's needed to pass test
- Don't anticipate future requirements
- Keep complexity low

#### 2. Clear Intent
- Code should clearly express its purpose
- Use descriptive variable names
- Add comments for complex logic

#### 3. Single Responsibility
- Each class/method has one clear purpose
- Avoid God classes
- Keep methods short

## Integration with Project Tools

### Test Runner Script
The `./scripts/test-runner.sh` automates the TDD cycle:

```bash
# Run full TDD cycle
./scripts/test-runner.sh

# Run with E2E tests
./scripts/test-runner.sh --full
```

### Coverage Requirements
- **Unit Tests**: 90% coverage minimum
- **Feature Tests**: 85% coverage minimum  
- **Integration Tests**: 80% coverage minimum

### Quality Gates
- All tests must pass before refactoring
- Code coverage targets must be met
- Performance benchmarks must be maintained

## Common TDD Patterns for POS System

### 1. Model Tests
```php
public function test_product_quantity_decreases_when_order_is_created()
{
    $product = Product::factory()->create(['quantity' => 100]);
    $order = Order::factory()->create();
    
    $order->addItem($product, 5);
    $order->save();
    
    $product->refresh();
    $this->assertEquals(95, $product->quantity);
}
```

### 2. Controller Tests
```php
public function test_order_creation_returns_correct_json_structure()
{
    $orderData = Order::factory()->make()->toArray();
    
    $response = $this->postJson('/api/orders', $orderData);
    
    $response->assertStatus(201)
            ->assertJsonStructure(['id', 'POS_number', 'total_amount']);
}
```

### 3. Service Tests
```php
public function test_offline_order_service_creates_order_and_queues_sync()
{
    $orderData = ['table_number' => 'Table 1', ...];
    $syncQueue = $this->mock(SyncQueue::class);
    
    $syncQueue->expects('push')
              ->once()
              ->with($this->anything());
    
    $order = $this->offlineOrderService->createOrder($orderData);
    
    $this->assertInstanceOf(Order::class, $order);
    $this->assertEquals('pending_sync', $order->state);
}
```

## Debugging TDD Tests

### When Tests Won't Pass

#### 1. Check Test Isolation
```php
public function setUp(): void
{
    parent::setUp();
    $this->artisan('db:seed'); // Reset database state
}
```

#### 2. Verify Test Data
```php
public function test_specific_scenario()
{
    dump($testData); // Debug test data
    $result = performOperation($testData);
    dump($result); // Debug result
    // assertions
}
```

#### 3. Check Dependencies
```php
public function test_service_integration()
{
    $this->withoutExceptionHandling(); // See actual errors
    
    // Test implementation
}
```

## TDD Metrics and Monitoring

### Success Metrics
- **Test Pass Rate**: 100% for all committed code
- **Coverage**: Meets minimum requirements
- **Cycle Time**: Average time from RED to GREEN
- **Refactor Frequency**: Number of refactor cycles per feature

### Monitoring Tools
- Test coverage reports in `storage/coverage/`
- Performance benchmarks in `logs/performance.log`
- Code quality metrics in `phpstan.neon`

## Troubleshooting

### Common Issues

#### 1. Tests Pass When They Shouldn't
- Check test is actually testing intended functionality
- Verify test data is realistic
- Ensure assertions are specific enough

#### 2. Tests Fail for Wrong Reasons
- Check environment configuration
- Verify database connections
- Ensure proper test isolation

#### 3. Refactoring Breaks Tests
- Run tests after each small change
- Check for unintended side effects
- Verify test setup is correct

This TDD workflow ensures high-quality, maintainable code throughout the Laravel POS 2026 upgrade project.