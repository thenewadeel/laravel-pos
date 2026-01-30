<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_customer_with_valid_data()
    {
        // RED: Customer model needs validation and proper fields
        $customerData = [
            'name' => 'John Doe',
            'membership_number' => 'MEM-001234',
            'email' => 'john.doe@example.com',
            'phone' => '+1-555-123-4567',
            'address' => '123 Main St, City, State 12345',
        ];
        
        $customer = Customer::create($customerData);
        
        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertDatabaseHas('customers', $customerData);
        $this->assertEquals('John Doe', $customer->name);
        $this->assertEquals('MEM-001234', $customer->membership_number);
        $this->assertEquals('john.doe@example.com', $customer->email);
    }

    /** @test */
    public function it_generates_unique_membership_number_automatically()
    {
        // RED: Membership number generation logic needed
        $customer = Customer::factory()->create();
        
        $this->assertNotNull($customer->membership_number);
        $this->assertMatchesRegularExpression('/^MEM-\d{8}$/', $customer->membership_number);
        
        // Test uniqueness
        $customer2 = Customer::factory()->create();
        $this->assertNotEquals($customer->membership_number, $customer2->membership_number);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // RED: Validation rules need to be implemented
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Customer::create([]); // Should fail with missing required fields
    }

    /** @test */
    public function it_validates_email_format()
    {
        // RED: Email validation needed
        $this->expectException(\Exception::class);
        
        Customer::factory()->create(['email' => 'invalid-email']);
    }

    /** @test */
    public function it_validates_phone_number_format()
    {
        // RED: Phone validation needed
        $this->expectException(\Exception::class);
        
        Customer::factory()->create(['phone' => 'invalid-phone']);
    }

    /** @test */
    public function it_can_have_multiple_orders()
    {
        // RED: Customer-Order relationship needed
        $customer = Customer::factory()->create();
        $order1 = Order::factory()->create(['customer_id' => $customer->id]);
        $order2 = Order::factory()->create(['customer_id' => $customer->id]);
        $order3 = Order::factory()->create(['customer_id' => $customer->id]);
        
        $this->assertCount(3, $customer->orders);
        $this->assertTrue($customer->orders->contains($order1));
        $this->assertTrue($customer->orders->contains($order2));
        $this->assertTrue($customer->orders->contains($order3));
    }

    /** @test */
    public function it_can_calculate_total_orders_amount()
    {
        // RED: Customer order total calculation needed
        $customer = Customer::factory()->create();
        
        $order1 = Order::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 50.00,
        ]);
        $order2 = Order::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 75.00,
        ]);
        $order3 = Order::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 25.00,
        ]);
        
        $totalSpent = $customer->getTotalOrdersAmount();
        
        $this->assertEquals(150.00, $totalSpent); // 50 + 75 + 25
    }

    /** @test */
    public function it_can_count_orders_by_status()
    {
        // RED: Order status counting needed
        $customer = Customer::factory()->create();
        
        Order::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'state' => 'closed',
        ]);
        Order::factory()->count(2)->create([
            'customer_id' => $customer->id,
            'state' => 'preparing',
        ]);
        Order::factory()->count(1)->create([
            'customer_id' => $customer->id,
            'state' => 'served',
        ]);
        
        $statusCounts = $customer->getOrdersByStatus();
        
        $this->assertEquals(3, $statusCounts['closed']);
        $this->assertEquals(2, $statusCounts['preparing']);
        $this->assertEquals(1, $statusCounts['served']);
        $this->assertEquals(0, $statusCounts['wastage'] ?? 0);
    }

    /** @test */
    public function it_can_get_average_order_value()
    {
        // RED: Average order value calculation needed
        $customer = Customer::factory()->create();
        
        Order::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 100.00,
        ]);
        Order::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 50.00,
        ]);
        Order::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 150.00,
        ]);
        
        $averageValue = $customer->getAverageOrderValue();
        
        $this->assertEquals(100.00, $averageValue); // (100 + 50 + 150) / 3
    }

    /** @test */
    public function it_can_check_if_customer_is_vip()
    {
        // RED: VIP customer detection logic needed
        $regularCustomer = Customer::factory()->create();
        $vipCustomer = Customer::factory()->create();
        
        // Give VIP customer high-value orders
        Order::factory()->count(10)->create([
            'customer_id' => $vipCustomer->id,
            'total_amount' => 200.00,
        ]);
        
        // Give regular customer low-value orders
        Order::factory()->count(5)->create([
            'customer_id' => $regularCustomer->id,
            'total_amount' => 25.00,
        ]);
        
        $this->assertFalse($regularCustomer->isVIP());
        $this->assertTrue($vipCustomer->isVIP());
    }

    /** @test */
    public function it_can_get_full_name()
    {
        // RED: Full name accessor needed
        $customer = Customer::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        
        $fullName = $customer->getFullName();
        
        $this->assertEquals('John Doe', $fullName);
    }

    /** @test */
    public function it_can_scope_by_membership_number()
    {
        // RED: Membership number scope needed
        $customer1 = Customer::factory()->create(['membership_number' => 'MEM-111111']);
        $customer2 = Customer::factory()->create(['membership_number' => 'MEM-222222']);
        $customer3 = Customer::factory()->create(['membership_number' => 'MEM-333333']);
        
        $foundCustomers = Customer::byMembershipNumber('MEM-222222')->get();
        
        $this->assertCount(1, $foundCustomers);
        $this->assertEquals($customer2->id, $foundCustomers->first()->id);
    }

    /** @test */
    public function it_can_search_customers_by_name()
    {
        // RED: Customer search functionality needed
        $customer1 = Customer::factory()->create(['name' => 'Alice Johnson']);
        $customer2 = Customer::factory()->create(['name' => 'Bob Smith']);
        $customer3 = Customer::factory()->create(['name' => 'Charlie Brown']);
        
        $aliceResults = Customer::search('Alice')->get();
        $johnResults = Customer::search('Johnson')->get();
        $allResults = Customer::search('J')->get();
        
        $this->assertCount(1, $aliceResults);
        $this->assertEquals($customer1->id, $aliceResults->first()->id);
        $this->assertCount(1, $johnResults); // Alice Johnson only
        $this->assertCount(3, $allResults); // All contain 'j' or 'J'
    }

    /** @test */
    public function it_can_search_customers_by_phone()
    {
        // RED: Phone search functionality needed
        $customer1 = Customer::factory()->create(['phone' => '+1-555-123-4567']);
        $customer2 = Customer::factory()->create(['phone' => '+1-555-987-6543']);
        
        $foundCustomers = Customer::searchByPhone('555-123')->get();
        
        $this->assertCount(1, $foundCustomers);
        $this->assertEquals($customer1->id, $foundCustomers->first()->id);
    }

    /** @test */
    public function it_can_get_most_recent_order()
    {
        // RED: Recent order logic needed
        $customer = Customer::factory()->create();
        
        $oldOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDays(30),
        ]);
        $recentOrder1 = Order::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDays(5),
        ]);
        $recentOrder2 = Order::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDays(1),
        ]);
        
        $mostRecentOrder = $customer->getMostRecentOrder();
        
        $this->assertEquals($recentOrder2->id, $mostRecentOrder->id);
    }

    /** @test */
    public function it_can_get_order_history_summary()
    {
        // RED: Order history summary needed
        $customer = Customer::factory()->create();
        
        Order::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 50.00,
            'state' => 'closed',
            'type' => 'dine-in',
            'created_at' => now()->subDays(10),
        ]);
        Order::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 75.00,
            'state' => 'served',
            'type' => 'take-away',
            'created_at' => now()->subDays(5),
        ]);
        Order::factory()->create([
            'customer_id' => $customer->id,
            'total_amount' => 25.00,
            'state' => 'preparing',
            'type' => 'dine-in',
            'created_at' => now()->subDays(1),
        ]);
        
        $history = $customer->getOrderHistorySummary();
        
        $this->assertCount(3, $history['orders']);
        $this->assertEquals(150.00, $history['total_amount']);
        $this->assertEquals(50.00, $history['average_amount']);
        $this->assertEquals(1, $history['dine_in_count']);
        $this->assertEquals(1, $history['take_away_count']);
        $this->assertEquals(1, $history['delivery_count'] ?? 0);
    }

    /** @test */
    public function it_soft_deletes_customers()
    {
        // RED: Soft delete functionality needed
        $customer = Customer::factory()->create();
        
        $customer->delete();
        
        // Should be soft deleted, not actually deleted
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertNotNull($customer->deleted_at);
        
        // Should not appear in regular queries
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_can_restore_soft_deleted_customers()
    {
        // RED: Soft delete restore needed
        $customer = Customer::factory()->create();
        $customer->delete();
        
        $customer->restore();
        
        $this->assertNotSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertNull($customer->deleted_at);
    }

    /** @test */
    public function it_logs_activity_when_created_updated_deleted()
    {
        // RED: Comprehensive activity logging needed
        $customer = Customer::factory()->create();
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'description' => 'created',
        ]);
        
        $customer->update(['name' => 'Updated Customer']);
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'description' => 'updated',
        ]);
        
        $customer->delete();
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'description' => 'deleted',
        ]);
    }

    /** @test */
    public function it_validates_unique_membership_number()
    {
        // RED: Unique membership validation needed
        $existingCustomer = Customer::factory()->create(['membership_number' => 'MEM-001234']);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Membership number already exists');
        
        Customer::factory()->create(['membership_number' => 'MEM-001234']);
    }

    /** @test */
    public function it_validates_unique_email()
    {
        // RED: Unique email validation needed
        $existingCustomer = Customer::factory()->create(['email' => 'test@example.com']);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Email already exists');
        
        Customer::factory()->create(['email' => 'test@example.com']);
    }

    /** @test */
    public function it_can_get_customer_statistics()
    {
        // RED: Customer statistics method needed
        $customer = Customer::factory()->create();
        
        Order::factory()->count(10)->create([
            'customer_id' => $customer->id,
            'total_amount' => 50.00,
            'state' => 'closed',
        ]);
        
        Order::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'total_amount' => 25.00,
            'state' => 'preparing',
        ]);
        
        $stats = $customer->getStatistics();
        
        $this->assertEquals(13, $stats['total_orders']);
        $this->assertEquals(10, $stats['completed_orders']);
        $this->assertEquals(3, $stats['pending_orders']);
        $this->assertEquals(600.00, $stats['total_spent']); // (10 * 50) + (3 * 25)
        $this->assertEquals(46.15, $stats['average_order_value']); // 600 / 13
    }

    /** @test */
    public function it_can_scope_by_vip_status()
    {
        // RED: VIP scope needed
        $regularCustomer = Customer::factory()->create();
        $vipCustomer = Customer::factory()->create();
        
        // Give VIP customer high-value orders
        Order::factory()->count(5)->create([
            'customer_id' => $vipCustomer->id,
            'total_amount' => 200.00,
        ]);
        
        $vipCustomers = Customer::vip()->get();
        $regularCustomers = Customer::regular()->get();
        
        $this->assertCount(1, $vipCustomers);
        $this->assertCount(1, $regularCustomers);
        $this->assertEquals($vipCustomer->id, $vipCustomers->first()->id);
        $this->assertEquals($regularCustomer->id, $regularCustomers->first()->id);
    }

    /** @test */
    public function it_can_get_loyalty_tier()
    {
        // RED: Loyalty tier calculation needed
        $bronzeCustomer = Customer::factory()->create();
        $silverCustomer = Customer::factory()->create();
        $goldCustomer = Customer::factory()->create();
        
        // Bronze: 5 orders, $25 average
        Order::factory()->count(5)->create([
            'customer_id' => $bronzeCustomer->id,
            'total_amount' => 25.00,
            'state' => 'closed',
        ]);
        
        // Silver: 15 orders, $50 average
        Order::factory()->count(15)->create([
            'customer_id' => $silverCustomer->id,
            'total_amount' => 50.00,
            'state' => 'closed',
        ]);
        
        // Gold: 25 orders, $100 average
        Order::factory()->count(25)->create([
            'customer_id' => $goldCustomer->id,
            'total_amount' => 100.00,
            'state' => 'closed',
        ]);
        
        $this->assertEquals('bronze', $bronzeCustomer->getLoyaltyTier());
        $this->assertEquals('silver', $silverCustomer->getLoyaltyTier());
        $this->assertEquals('gold', $goldCustomer->getLoyaltyTier());
    }
}