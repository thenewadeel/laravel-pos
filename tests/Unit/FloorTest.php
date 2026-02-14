<?php

namespace Tests\Unit;

use App\Models\Floor;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FloorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_floor()
    {
        $shop = Shop::factory()->create();
        
        $floor = Floor::create([
            'shop_id' => $shop->id,
            'name' => 'Ground Floor',
            'description' => 'Main dining area',
            'sort_order' => 1,
            'is_active' => true,
            'layout_config' => ['width' => 1000, 'height' => 800],
        ]);
        
        $this->assertDatabaseHas('floors', [
            'id' => $floor->id,
            'shop_id' => $shop->id,
            'name' => 'Ground Floor',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_shop()
    {
        $shop = Shop::factory()->create();
        $floor = Floor::factory()->create(['shop_id' => $shop->id]);
        
        $this->assertInstanceOf(Shop::class, $floor->shop);
        $this->assertEquals($shop->id, $floor->shop->id);
    }

    /** @test */
    public function it_has_many_tables()
    {
        $floor = Floor::factory()->create();
        
        $table1 = \App\Models\RestaurantTable::factory()->create(['floor_id' => $floor->id]);
        $table2 = \App\Models\RestaurantTable::factory()->create(['floor_id' => $floor->id]);
        
        $this->assertCount(2, $floor->tables);
        $this->assertTrue($floor->tables->contains($table1));
        $this->assertTrue($floor->tables->contains($table2));
    }

    /** @test */
    public function it_can_scope_active_floors()
    {
        $activeFloor = Floor::factory()->create(['is_active' => true]);
        $inactiveFloor = Floor::factory()->create(['is_active' => false]);
        
        $activeFloors = Floor::active()->get();
        
        $this->assertCount(1, $activeFloors);
        $this->assertTrue($activeFloors->contains($activeFloor));
        $this->assertFalse($activeFloors->contains($inactiveFloor));
    }

    /** @test */
    public function it_can_scope_by_shop()
    {
        $shop1 = Shop::factory()->create();
        $shop2 = Shop::factory()->create();
        
        $floor1 = Floor::factory()->create(['shop_id' => $shop1->id]);
        $floor2 = Floor::factory()->create(['shop_id' => $shop2->id]);
        
        $shop1Floors = Floor::forShop($shop1->id)->get();
        
        $this->assertCount(1, $shop1Floors);
        $this->assertTrue($shop1Floors->contains($floor1));
        $this->assertFalse($shop1Floors->contains($floor2));
    }

    /** @test */
    public function it_orders_by_sort_order()
    {
        $floor1 = Floor::factory()->create(['sort_order' => 3]);
        $floor2 = Floor::factory()->create(['sort_order' => 1]);
        $floor3 = Floor::factory()->create(['sort_order' => 2]);
        
        $floors = Floor::ordered()->get();
        
        $this->assertEquals($floor2->id, $floors->first()->id);
        $this->assertEquals($floor1->id, $floors->last()->id);
    }

    /** @test */
    public function it_can_get_table_count()
    {
        $floor = Floor::factory()->create();
        
        \App\Models\RestaurantTable::factory()->count(5)->create(['floor_id' => $floor->id]);
        
        $this->assertEquals(5, $floor->getTableCount());
    }

    /** @test */
    public function it_can_get_available_table_count()
    {
        $floor = Floor::factory()->create();
        
        \App\Models\RestaurantTable::factory()->count(3)->create([
            'floor_id' => $floor->id,
            'status' => 'available'
        ]);
        \App\Models\RestaurantTable::factory()->count(2)->create([
            'floor_id' => $floor->id,
            'status' => 'occupied'
        ]);
        
        $this->assertEquals(3, $floor->getAvailableTableCount());
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $floor = Floor::factory()->create();
        
        $floor->delete();
        
        $this->assertSoftDeleted('floors', ['id' => $floor->id]);
        $this->assertNull(Floor::find($floor->id));
        $this->assertNotNull(Floor::withTrashed()->find($floor->id));
    }
}
