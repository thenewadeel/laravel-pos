<?php

namespace Tests\Unit;

use App\Models\Floor;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_table()
    {
        $floor = Floor::factory()->create();
        
        $table = RestaurantTable::create([
            'floor_id' => $floor->id,
            'table_number' => 'T-001',
            'name' => 'Window Table',
            'capacity' => 4,
            'status' => 'available',
            'position_x' => 100.50,
            'position_y' => 200.75,
            'width' => 120.00,
            'height' => 80.00,
            'shape' => 'rectangle',
            'is_active' => true,
            'metadata' => ['features' => ['window_view', 'wheelchair_accessible']],
        ]);
        
        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'floor_id' => $floor->id,
            'table_number' => 'T-001',
            'status' => 'available',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_floor()
    {
        $floor = Floor::factory()->create();
        $table = RestaurantTable::factory()->create(['floor_id' => $floor->id]);
        
        $this->assertInstanceOf(Floor::class, $table->floor);
        $this->assertEquals($floor->id, $table->floor->id);
    }

    /** @test */
    public function it_has_many_orders()
    {
        $table = RestaurantTable::factory()->create();
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();
        
        $table->orders()->attach($order1->id, ['started_at' => now(), 'is_active' => true]);
        $table->orders()->attach($order2->id, ['started_at' => now(), 'is_active' => true]);
        
        $this->assertCount(2, $table->orders);
    }

    /** @test */
    public function it_can_get_active_order()
    {
        $table = RestaurantTable::factory()->create();
        $activeOrder = Order::factory()->create();
        $completedOrder = Order::factory()->create();
        
        $table->orders()->attach($activeOrder->id, ['started_at' => now(), 'is_active' => true]);
        $table->orders()->attach($completedOrder->id, ['started_at' => now()->subHour(), 'is_active' => false, 'ended_at' => now()]);
        
        $this->assertEquals($activeOrder->id, $table->getActiveOrder()->id);
    }

    /** @test */
    public function it_can_scope_by_status()
    {
        $availableTable = RestaurantTable::factory()->create(['status' => 'available']);
        $occupiedTable = RestaurantTable::factory()->create(['status' => 'occupied']);
        
        $availableTables = RestaurantTable::byStatus('available')->get();
        
        $this->assertCount(1, $availableTables);
        $this->assertTrue($availableTables->contains($availableTable));
        $this->assertFalse($availableTables->contains($occupiedTable));
    }

    /** @test */
    public function it_can_scope_available_tables()
    {
        $availableTable = RestaurantTable::factory()->create(['status' => 'available']);
        $occupiedTable = RestaurantTable::factory()->create(['status' => 'occupied']);
        $reservedTable = RestaurantTable::factory()->create(['status' => 'reserved']);
        
        $availableTables = RestaurantTable::available()->get();
        
        $this->assertCount(1, $availableTables);
        $this->assertTrue($availableTables->contains($availableTable));
    }

    /** @test */
    public function it_can_scope_by_floor()
    {
        $floor1 = Floor::factory()->create();
        $floor2 = Floor::factory()->create();
        
        $table1 = RestaurantTable::factory()->create(['floor_id' => $floor1->id]);
        $table2 = RestaurantTable::factory()->create(['floor_id' => $floor2->id]);
        
        $floor1Tables = RestaurantTable::forFloor($floor1->id)->get();
        
        $this->assertCount(1, $floor1Tables);
        $this->assertTrue($floor1Tables->contains($table1));
        $this->assertFalse($floor1Tables->contains($table2));
    }

    /** @test */
    public function it_can_update_status()
    {
        $table = RestaurantTable::factory()->create(['status' => 'available']);
        
        $table->updateStatus('occupied');
        
        $this->assertEquals('occupied', $table->fresh()->status);
    }

    /** @test */
    public function it_can_assign_order()
    {
        $table = RestaurantTable::factory()->create(['status' => 'available']);
        $order = Order::factory()->create();
        
        $table->assignOrder($order->id);
        
        $this->assertDatabaseHas('table_orders', [
            'table_id' => $table->id,
            'order_id' => $order->id,
            'is_active' => true,
        ]);
        
        $this->assertEquals('occupied', $table->fresh()->status);
    }

    /** @test */
    public function it_can_release_table()
    {
        $table = RestaurantTable::factory()->create(['status' => 'occupied']);
        $order = Order::factory()->create();
        
        $table->orders()->attach($order->id, ['started_at' => now(), 'is_active' => true]);
        
        $table->release();
        
        $this->assertEquals('available', $table->fresh()->status);
        $this->assertDatabaseHas('table_orders', [
            'table_id' => $table->id,
            'order_id' => $order->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function it_can_check_if_occupied()
    {
        $availableTable = RestaurantTable::factory()->create(['status' => 'available']);
        $occupiedTable = RestaurantTable::factory()->create(['status' => 'occupied']);
        
        $this->assertFalse($availableTable->isOccupied());
        $this->assertTrue($occupiedTable->isOccupied());
    }

    /** @test */
    public function it_can_get_occupation_duration()
    {
        $table = RestaurantTable::factory()->create(['status' => 'occupied']);
        $order = Order::factory()->create();
        
        $table->orders()->attach($order->id, [
            'started_at' => now()->subMinutes(45),
            'is_active' => true
        ]);
        
        $duration = $table->getOccupationDuration();
        
        $this->assertGreaterThanOrEqual(45, $duration);
        $this->assertLessThan(46, $duration);
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $table = RestaurantTable::factory()->create();
        
        $table->delete();
        
        $this->assertSoftDeleted('restaurant_tables', ['id' => $table->id]);
        $this->assertNull(RestaurantTable::find($table->id));
        $this->assertNotNull(RestaurantTable::withTrashed()->find($table->id));
    }

    /** @test */
    public function it_has_unique_table_number_per_floor()
    {
        $floor = Floor::factory()->create();
        
        RestaurantTable::factory()->create([
            'floor_id' => $floor->id,
            'table_number' => 'T-001'
        ]);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        RestaurantTable::factory()->create([
            'floor_id' => $floor->id,
            'table_number' => 'T-001'
        ]);
    }
}
