<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Shop;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user_with_valid_data()
    {
        // RED: User model needs validation and proper fields
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'current_shop_id' => null,
        ];
        
        $user = User::create($userData);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', $userData);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('john.doe@example.com', $user->email);
    }

    /** @test */
    public function it_generates_full_name_automatically()
    {
        // RED: Full name accessor needed
        $user = User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
        ]);
        
        $fullName = $user->getFullName();
        
        $this->assertEquals('Alice Johnson', $fullName);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // RED: Validation rules need to be implemented
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::create([]); // Should fail with missing required fields
    }

    /** @test */
    public function it_validates_email_format()
    {
        // RED: Email validation needed
        $this->expectException(\Exception::class);
        
        User::factory()->create(['email' => 'invalid-email']);
    }

    /** @test */
    public function it_validates_unique_email()
    {
        // RED: Unique email validation needed
        $existingUser = User::factory()->create(['email' => 'test@example.com']);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Email already exists');
        
        User::factory()->create(['email' => 'test@example.com']);
    }

    /** @test */
    public function it_can_belong_to_multiple_shops()
    {
        // RED: User-Shop relationship needed
        $user = User::factory()->create();
        $shop1 = Shop::factory()->create();
        $shop2 = Shop::factory()->create();
        
        $user->shops()->attach([$shop1->id, $shop2->id]);
        
        $this->assertCount(2, $user->shops);
        $this->assertTrue($user->shops->contains($shop1));
        $this->assertTrue($user->shops->contains($shop2));
    }

    /** @test */
    public function it_can_have_favorite_printer()
    {
        // RED: Printer preference functionality needed
        $user = User::factory()->create([
            'favorite_printer_ip' => '192.168.1.100',
        ]);
        
        $this->assertEquals('192.168.1.100', $user->favorite_printer_ip);
    }

    /** @test */
    public function it_can_set_current_shop()
    {
        // RED: Current shop assignment needed
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        
        $user->setCurrentShop($shop->id);
        
        $this->assertEquals($shop->id, $user->current_shop_id);
    }

    /** @test */
    public function it_can_get_active_shop()
    {
        // RED: Active shop retrieval needed
        $user = User::factory()->create();
        $shop1 = Shop::factory()->create();
        $shop2 = Shop::factory()->create();
        
        $user->shops()->attach([$shop1->id, $shop2->id]);
        $user->setCurrentShop($shop2->id);
        
        $activeShop = $user->getActiveShop();
        
        $this->assertEquals($shop2->id, $activeShop->id);
        $this->assertEquals($shop2->name, $activeShop->name);
    }

    /** @test */
    public function it_can_check_shop_access()
    {
        // RED: Shop access checking needed
        $user = User::factory()->create();
        $authorizedShop = Shop::factory()->create();
        $unauthorizedShop = Shop::factory()->create();
        
        $user->shops()->attach([$authorizedShop->id]);
        
        $this->assertTrue($user->hasShopAccess($authorizedShop->id));
        $this->assertFalse($user->hasShopAccess($unauthorizedShop->id));
    }

    /** @test */
    public function it_can_create_orders()
    {
        // RED: User-Order relationship needed
        $user = User::factory()->create();
        
        $order1 = Order::factory()->create(['user_id' => $user->id]);
        $order2 = Order::factory()->create(['user_id' => $user->id]);
        $order3 = Order::factory()->create(['user_id' => $user->id]);
        
        $this->assertCount(3, $user->orders);
        $this->assertTrue($user->orders->contains($order1));
        $this->assertTrue($user->orders->contains($order2));
        $this->assertTrue($user->orders->contains($order3));
    }

    /** @test */
    public function it_can_get_orders_by_shop()
    {
        // RED: Shop-specific order retrieval needed
        $user = User::factory()->create();
        $shop1 = Shop::factory()->create();
        $shop2 = Shop::factory()->create();
        
        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'shop_id' => $shop1->id,
        ]);
        Order::factory()->count(2)->create([
            'user_id' => $user->id,
            'shop_id' => $shop2->id,
        ]);
        
        $shop1Orders = $user->getOrdersByShop($shop1->id);
        $shop2Orders = $user->getOrdersByShop($shop2->id);
        
        $this->assertCount(3, $shop1Orders);
        $this->assertCount(2, $shop2Orders);
        
        foreach ($shop1Orders as $order) {
            $this->assertEquals($shop1->id, $order->shop_id);
        }
    }

    /** @test */
    public function it_can_get_order_statistics()
    {
        // RED: Order statistics calculation needed
        $user = User::factory()->create();
        
        Order::factory()->count(10)->create([
            'user_id' => $user->id,
            'total_amount' => 50.00,
            'state' => 'closed',
        ]);
        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'total_amount' => 75.00,
            'state' => 'preparing',
        ]);
        
        $stats = $user->getOrderStatistics();
        
        $this->assertEquals(13, $stats['total_orders']);
        $this->assertEquals(10, $stats['completed_orders']);
        $this->assertEquals(3, $stats['pending_orders']);
        $this->assertEquals(725.00, $stats['total_revenue']); // (10 * 50) + (3 * 75)
    }

    /** @test */
    public function it_can_calculate_average_order_value()
    {
        // RED: Average order value calculation needed
        $user = User::factory()->create();
        
        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 100.00,
        ]);
        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 50.00,
        ]);
        Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 150.00,
        ]);
        
        $averageValue = $user->getAverageOrderValue();
        
        $this->assertEquals(100.00, $averageValue); // (100 + 50 + 150) / 3
    }

    /** @test */
    public function it_can_get_most_recent_order()
    {
        // RED: Recent order retrieval needed
        $user = User::factory()->create();
        
        $oldOrder = Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(30),
        ]);
        $recentOrder1 = Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(5),
        ]);
        $recentOrder2 = Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(1),
        ]);
        
        $mostRecentOrder = $user->getMostRecentOrder();
        
        $this->assertEquals($recentOrder2->id, $mostRecentOrder->id);
    }

    /** @test */
    public function it_can_check_if_is_waiter()
    {
        // RED: Waiter role detection needed
        $waiterUser = User::factory()->create();
        $adminUser = User::factory()->create();
        
        // Assign waiter role (this would require role system)
        $waiterUser->assignRole('waiter');
        $adminUser->assignRole('admin');
        
        $this->assertTrue($waiterUser->isWaiter());
        $this->assertFalse($adminUser->isWaiter());
    }

    /** @test */
    public function it_can_check_if_is_manager()
    {
        // RED: Manager role detection needed
        $managerUser = User::factory()->create();
        $cashierUser = User::factory()->create();
        
        $managerUser->assignRole('manager');
        $cashierUser->assignRole('cashier');
        
        $this->assertTrue($managerUser->isManager());
        $this->assertFalse($cashierUser->isManager());
    }

    /** @test */
    public function it_can_get_working_hours()
    {
        // RED: Working hours calculation needed
        $user = User::factory()->create();
        
        Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->setTime(9, 0),
            'updated_at' => now()->setTime(17, 0),
        ]);
        Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(1)->setTime(10, 0),
            'updated_at' => now()->subDays(1)->setTime(16, 30),
        ]);
        
        $workingHours = $user->getWorkingHours(now()->subDays(7), now());
        
        $this->assertEquals(15.5, $workingHours); // 8 hours yesterday + 7.5 hours today
    }

    /** @test */
    public function it_can_get_shift_summary()
    {
        // RED: Shift summary functionality needed
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        
        $user->shops()->attach($shop->id);
        
        Order::factory()->count(5)->create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'total_amount' => 50.00,
            'state' => 'closed',
            'created_at' => now()->setTime(9, 0),
        ]);
        
        Order::factory()->create([
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'total_amount' => 75.00,
            'state' => 'closed',
            'created_at' => now()->setTime(14, 0),
        ]);
        
        $shiftSummary = $user->getShiftSummary($shop->id, now());
        
        $this->assertEquals(6, $shiftSummary['orders_count']);
        $this->assertEquals(325.00, $shiftSummary['total_revenue']); // (5 * 50) + 75
        $this->assertEquals(54.17, $shiftSummary['average_order_value']); // 325 / 6
    }

    /** @test */
    public function it_can_scope_by_shop()
    {
        // RED: Shop-based query scope needed
        $user = User::factory()->create();
        $shop1 = Shop::factory()->create();
        $shop2 = Shop::factory()->create();
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        $user1->shops()->attach([$shop1->id]);
        $user2->shops()->attach([$shop1->id]);
        $user3->shops()->attach([$shop2->id]);
        
        $shop1Users = User::byShop($shop1->id)->get();
        $shop2Users = User::byShop($shop2->id)->get();
        
        $this->assertCount(2, $shop1Users);
        $this->assertCount(1, $shop2Users);
        
        $this->assertTrue($shop1Users->contains($user1));
        $this->assertTrue($shop1Users->contains($user2));
        $this->assertTrue($shop2Users->contains($user3));
    }

    /** @test */
    public function it_can_search_by_name()
    {
        // RED: User search functionality needed
        $user1 = User::factory()->create(['first_name' => 'Alice', 'last_name' => 'Johnson']);
        $user2 = User::factory()->create(['first_name' => 'Bob', 'last_name' => 'Smith']);
        $user3 = User::factory()->create(['first_name' => 'Charlie', 'last_name' => 'Brown']);
        
        $aliceResults = User::search('Alice')->get();
        $smithResults = User::search('Smith')->get();
        $allResults = User::search('o')->get();
        
        $this->assertCount(1, $aliceResults);
        $this->assertEquals($user1->id, $aliceResults->first()->id);
        $this->assertCount(1, $smithResults);
        $this->assertEquals($user2->id, $smithResults->first()->id);
        $this->assertCount(2, $allResults); // Alice Johnson, Bob Smith
    }

    /** @test */
    public function it_soft_deletes_users()
    {
        // RED: Soft delete functionality needed
        $user = User::factory()->create();
        
        $user->delete();
        
        // Should be soft deleted, not actually deleted
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertNotNull($user->deleted_at);
        
        // Should not appear in regular queries
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_can_restore_soft_deleted_users()
    {
        // RED: Soft delete restore needed
        $user = User::factory()->create();
        $user->delete();
        
        $user->restore();
        
        $this->assertNotSoftDeleted('users', ['id' => $user->id]);
        $this->assertNull($user->deleted_at);
    }

    /** @test */
    public function it_logs_activity_when_created_updated_deleted()
    {
        // RED: Comprehensive activity logging needed
        $user = User::factory()->create();
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'description' => 'created',
        ]);
        
        $user->update(['first_name' => 'Updated User']);
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'description' => 'updated',
        ]);
        
        $user->delete();
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'description' => 'deleted',
        ]);
    }

    /** @test */
    public function it_can_get_performance_metrics()
    {
        // RED: Performance metrics calculation needed
        $user = User::factory()->create();
        
        Order::factory()->count(20)->create([
            'user_id' => $user->id,
            'total_amount' => 50.00,
            'state' => 'closed',
            'created_at' => now()->subDays(30),
        ]);
        
        $metrics = $user->getPerformanceMetrics(now()->subDays(30), now());
        
        $this->assertEquals(20, $metrics['orders_processed']);
        $this->assertEquals(1000.00, $metrics['revenue_generated']);
        $this->assertEquals(50.00, $metrics['average_order_value']);
        $this->assertEquals(1.0, $metrics['orders_per_day']); // 20 / 30 days (approximate)
    }

    /** @test */
    public function it_can_check_password_strength()
    {
        // RED: Password strength validation needed
        $weakPassword = '123456';
        $strongPassword = 'MyStr0ngP@ssw0rd!';
        
        $this->assertFalse(User::isPasswordStrong($weakPassword));
        $this->assertTrue(User::isPasswordStrong($strongPassword));
    }

    /** @test */
    public function it_can_get_user_permissions()
    {
        // RED: User permissions system needed
        $user = User::factory()->create();
        
        $user->assignRole('waiter');
        $permissions = $user->getPermissions();
        
        $this->assertTrue(in_array('create_orders', $permissions));
        $this->assertTrue(in_array('view_orders', $permissions));
        $this->assertFalse(in_array('delete_shop', $permissions));
        $this->assertFalse(in_array('manage_users', $permissions));
    }
}