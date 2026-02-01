<?php

namespace Tests\Unit\Livewire;

use App\Livewire\TabletOrderComponent;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TabletOrderComponentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Shop $shop;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['type' => 'cashier']);
        $this->shop = Shop::factory()->create();
        $this->customer = Customer::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 10.00,
            'quantity' => 100,
            'aval_status' => true
        ]);
    }

    /** @test */
    public function it_can_add_items_to_order()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-001')
            ->set('shopId', $this->shop->id)
            ->call('addItem', $this->product->id, 2)
            ->assertSet('orderItems', function ($items) {
                return count($items) === 1 && $items[0]['quantity'] === 2;
            })
            ->assertSet('totalAmount', 20.00);
    }

    /** @test */
    public function it_can_remove_items_from_order()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-002')
            ->set('shopId', $this->shop->id)
            ->call('addItem', $this->product->id, 2)
            ->call('removeItem', 0)
            ->assertSet('orderItems', [])
            ->assertSet('totalAmount', 0.00);
    }

    /** @test */
    public function it_can_update_item_quantity()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-003')
            ->set('shopId', $this->shop->id)
            ->call('addItem', $this->product->id, 2)
            ->call('updateQuantity', 0, 5)
            ->assertSet('orderItems', function ($items) {
                return $items[0]['quantity'] === 5;
            })
            ->assertSet('totalAmount', 50.00);
    }

    /** @test */
    public function it_validates_sufficient_stock_before_adding_item()
    {
        $lowStockProduct = Product::factory()->create([
            'price' => 15.00,
            'quantity' => 3
        ]);

        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-004')
            ->set('shopId', $this->shop->id)
            ->call('addItem', $lowStockProduct->id, 5)
            ->assertHasErrors(['orderItems'])
            ->assertSet('orderItems', []);
    }

    /** @test */
    public function it_can_set_table_number()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-005')
            ->set('shopId', $this->shop->id)
            ->set('tableNumber', 'Table 12')
            ->assertSet('tableNumber', 'Table 12');
    }

    /** @test */
    public function it_can_set_waiter_name()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-006')
            ->set('shopId', $this->shop->id)
            ->set('waiterName', 'John Waiter')
            ->assertSet('waiterName', 'John Waiter');
    }

    /** @test */
    public function it_can_set_order_type()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-007')
            ->set('shopId', $this->shop->id)
            ->set('orderType', 'take-away')
            ->assertSet('orderType', 'take-away');
    }

    /** @test */
    public function it_can_select_customer()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-008')
            ->set('shopId', $this->shop->id)
            ->set('customerId', $this->customer->id)
            ->assertSet('customerId', $this->customer->id);
    }

    /** @test */
    public function it_can_create_offline_order()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-009')
            ->set('shopId', $this->shop->id)
            ->set('tableNumber', 'Table 5')
            ->set('waiterName', 'Jane Waiter')
            ->set('orderType', 'dine-in')
            ->set('customerId', $this->customer->id)
            ->call('addItem', $this->product->id, 3)
            ->call('createOrder')
            ->assertSet('orderCreated', true)
            ->assertSet('localOrderId', function ($id) {
                return !empty($id);
            });

        // Verify order was created in database
        $this->assertDatabaseHas('orders', [
            'table_number' => 'Table 5',
            'waiter_name' => 'Jane Waiter',
            'type' => 'dine-in',
            'customer_id' => $this->customer->id,
            'sync_status' => 'pending_sync',
            'device_id' => 'tablet-test-009',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_before_creating_order()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-010')
            ->set('shopId', $this->shop->id)
            ->call('createOrder')
            ->assertHasErrors(['tableNumber', 'orderItems']);
    }

    /** @test */
    public function it_can_clear_order_after_creation()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-011')
            ->set('shopId', $this->shop->id)
            ->set('tableNumber', 'Table 8')
            ->set('waiterName', 'Test Waiter')
            ->call('addItem', $this->product->id, 2)
            ->call('createOrder')
            ->assertSet('orderItems', [])
            ->assertSet('tableNumber', '')
            ->assertSet('totalAmount', 0.00);
    }

    /** @test */
    public function it_generates_unique_local_order_id()
    {
        $component = Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-012')
            ->set('shopId', $this->shop->id);

        $localOrderId1 = $component->get('localOrderId');
        
        $component->call('generateLocalOrderId');
        
        $localOrderId2 = $component->get('localOrderId');
        
        $this->assertNotEquals($localOrderId1, $localOrderId2);
        $this->assertStringContainsString('tablet-test-012', $localOrderId2);
    }

    /** @test */
    public function it_shows_connection_status()
    {
        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-013')
            ->set('isOnline', true)
            ->assertSet('isOnline', true)
            ->assertSee('Connected');
    }

    /** @test */
    public function it_can_search_products()
    {
        $product2 = Product::factory()->create([
            'name' => 'Special Coffee',
            'price' => 5.00,
            'aval_status' => true
        ]);

        Livewire::actingAs($this->user)
            ->test(TabletOrderComponent::class)
            ->set('deviceId', 'tablet-test-014')
            ->set('shopId', $this->shop->id)
            ->set('productSearch', 'Coffee')
            ->assertSee('Special Coffee');
    }
}
