<?php

namespace Tests\Feature\API;

use App\Models\Floor;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FloorSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['type' => 'admin']);
        $this->shop = Shop::factory()->create();
    }

    /** @test */
    public function it_can_download_floor_data_for_offline_sync()
    {
        $floor = Floor::factory()->create(['shop_id' => $this->shop->id]);
        $tables = RestaurantTable::factory()->count(3)->create(['floor_id' => $floor->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/sync/floors?shop_id={$this->shop->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'floors' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'sort_order',
                            'tables' => [
                                '*' => [
                                    'id',
                                    'table_number',
                                    'name',
                                    'capacity',
                                    'status',
                                    'position_x',
                                    'position_y',
                                ]
                            ]
                        ]
                    ],
                    'sync_timestamp',
                ]
            ]);
    }

    /** @test */
    public function it_can_upload_offline_table_assignments()
    {
        $floor = Floor::factory()->create(['shop_id' => $this->shop->id]);
        $table = RestaurantTable::factory()->create([
            'floor_id' => $floor->id,
            'status' => 'available'
        ]);
        $order = Order::factory()->create(['shop_id' => $this->shop->id]);

        $syncData = [
            'shop_id' => $this->shop->id,
            'device_id' => 'tablet-001',
            'assignments' => [
                [
                    'table_id' => $table->id,
                    'order_id' => $order->id,
                    'assigned_at' => now()->toIso8601String(),
                    'synced_at' => now()->toIso8601String(),
                ]
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sync/tables/upload', $syncData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'processed' => 1,
                    'failed' => 0,
                ]
            ]);

        $this->assertDatabaseHas('table_orders', [
            'table_id' => $table->id,
            'order_id' => $order->id,
            'is_active' => true,
        ]);

        $this->assertEquals('occupied', $table->fresh()->status);
    }

    /** @test */
    public function it_can_get_sync_status_for_device()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/sync/floors/status?device_id=tablet-001');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'device_id',
                    'last_sync',
                    'pending_uploads',
                    'pending_downloads',
                    'sync_status',
                ]
            ]);
    }

    /** @test */
    public function it_handles_conflicts_when_table_already_occupied()
    {
        $floor = Floor::factory()->create(['shop_id' => $this->shop->id]);
        $table = RestaurantTable::factory()->create([
            'floor_id' => $floor->id,
            'status' => 'occupied'
        ]);
        $existingOrder = Order::factory()->create(['shop_id' => $this->shop->id]);
        $newOrder = Order::factory()->create(['shop_id' => $this->shop->id]);
        
        // Pre-assign table to existing order
        $table->assignOrder($existingOrder->id);

        $syncData = [
            'shop_id' => $this->shop->id,
            'device_id' => 'tablet-001',
            'assignments' => [
                [
                    'table_id' => $table->id,
                    'order_id' => $newOrder->id,
                    'assigned_at' => now()->subMinutes(5)->toIso8601String(),
                    'synced_at' => now()->toIso8601String(),
                ]
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sync/tables/upload', $syncData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'processed' => 0,
                    'failed' => 1,
                    'conflicts' => [
                        [
                            'table_id' => $table->id,
                            'reason' => 'table_already_occupied',
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_download_server_updates_since_last_sync()
    {
        $floor = Floor::factory()->create(['shop_id' => $this->shop->id]);
        $table = RestaurantTable::factory()->create(['floor_id' => $floor->id]);
        $order = Order::factory()->create(['shop_id' => $this->shop->id]);
        
        // Create assignment after a specific timestamp
        $lastSync = now()->subHour();
        $table->orders()->attach($order->id, [
            'started_at' => now()->subMinutes(30),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/sync/tables/download?shop_id={$this->shop->id}&since=" . urlencode($lastSync->toDateTimeString()));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'assignments' => [
                        '*' => [
                            'table_id',
                            'order_id',
                            'assigned_at',
                            'status',
                        ]
                    ],
                    'table_updates' => [
                        '*' => [
                            'id',
                            'status',
                            'updated_at',
                        ]
                    ],
                    'sync_timestamp',
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_sync_endpoints()
    {
        $response = $this->getJson('/api/v1/sync/floors');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/sync/tables/upload', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_sync_upload_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sync/tables/upload', [
                'shop_id' => '',
                'assignments' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shop_id', 'assignments']);
    }

    /** @test */
    public function it_can_acknowledge_received_sync_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sync/acknowledge', [
                'device_id' => 'tablet-001',
                'sync_timestamp' => now()->toIso8601String(),
                'received_items' => [1, 2, 3],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sync acknowledged',
            ]);
    }

    /** @test */
    public function it_returns_empty_sync_when_no_changes_since_last_sync()
    {
        $floor = Floor::factory()->create(['shop_id' => $this->shop->id]);
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/sync/tables/download?shop_id={$this->shop->id}&since=" . urlencode(now()->toDateTimeString()));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'assignments' => [],
                    'table_updates' => [],
                ]
            ]);
    }

    /** @test */
    public function it_can_batch_upload_multiple_assignments()
    {
        $floor = Floor::factory()->create(['shop_id' => $this->shop->id]);
        $tables = RestaurantTable::factory()->count(5)->create([
            'floor_id' => $floor->id,
            'status' => 'available'
        ]);
        $orders = Order::factory()->count(5)->create(['shop_id' => $this->shop->id]);

        $assignments = [];
        foreach ($tables as $index => $table) {
            $assignments[] = [
                'table_id' => $table->id,
                'order_id' => $orders[$index]->id,
                'assigned_at' => now()->subMinutes($index * 10)->toIso8601String(),
                'synced_at' => now()->toIso8601String(),
            ];
        }

        $syncData = [
            'shop_id' => $this->shop->id,
            'device_id' => 'tablet-001',
            'assignments' => $assignments,
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/sync/tables/upload', $syncData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'processed' => 5,
                    'failed' => 0,
                ]
            ]);

        foreach ($tables as $table) {
            $this->assertEquals('occupied', $table->fresh()->status);
        }
    }
}
