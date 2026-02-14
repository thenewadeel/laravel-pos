<?php

namespace Tests\Feature\API;

use App\Models\Order;
use App\Models\OrderSyncQueue;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $deviceId = 'test-device-001';

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $shop = Shop::factory()->create();
        $this->user->shops()->attach($shop);
        $this->user->current_shop_id = $shop->id;
        $this->actingAs($this->user);
    }

    /**
     * Test: Can upload offline orders via API
     * RED Phase: This should fail initially
     */
    public function test_can_upload_offline_orders_via_api()
    {
        $product = Product::factory()->create(['quantity' => 100]);
        
        $shop = $this->user->shops()->first();
        $offlineOrders = [
            [
                'local_order_id' => 'tablet-order-001',
                'table_number' => 'Table 5',
                'waiter_name' => 'John Waiter',
                'type' => 'dine-in',
                'customer_id' => null,
                'shop_id' => $shop->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit_price' => $product->price,
                    ]
                ],
                'total_amount' => $product->price * 2,
            ]
        ];

        $response = $this->postJson('/api/v1/sync/upload', [
            'device_id' => $this->deviceId,
            'orders' => $offlineOrders,
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'uploaded_count',
                        'orders' => [
                            '*' => [
                                'local_order_id',
                                'server_order_id',
                                'status',
                            ]
                        ]
                    ],
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'uploaded_count' => 1,
                    ],
                ]);
    }

    /**
     * Test: Can get sync status via API
     * RED Phase: This should fail initially
     */
    public function test_can_get_sync_status_via_api()
    {
        // Create some sync queue entries
        OrderSyncQueue::factory()->count(3)->create([
            'device_id' => $this->deviceId,
            'status' => 'pending',
        ]);
        
        OrderSyncQueue::factory()->count(2)->create([
            'device_id' => $this->deviceId,
            'status' => 'completed',
        ]);

        $response = $this->getJson('/api/v1/sync/status?device_id=' . $this->deviceId);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'device_id',
                        'total_orders',
                        'pending_count',
                        'completed_count',
                        'failed_count',
                        'conflict_count',
                        'last_sync_at',
                    ],
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'device_id' => $this->deviceId,
                        'total_orders' => 5,
                        'pending_count' => 3,
                        'completed_count' => 2,
                    ],
                ]);
    }

    /**
     * Test: Can download server updates via API
     * RED Phase: This should fail initially
     */
    public function test_can_download_server_updates_via_api()
    {
        // Create some orders that should be synced to tablet
        $orders = Order::factory()->count(3)->create([
            'sync_status' => 'synced',
            'device_id' => $this->deviceId,
        ]);

        $response = $this->postJson('/api/v1/sync/download', [
            'device_id' => $this->deviceId,
            'last_sync_at' => now()->subDay()->toIso8601String(),
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'orders' => [
                            '*' => [
                                'id',
                                'POS_number',
                                'table_number',
                                'waiter_name',
                                'type',
                                'status',
                                'total_amount',
                                'items',
                                'synced_at',
                            ]
                        ],
                        'products_updated',
                        'customers_updated',
                    ],
                ])
                ->assertJson([
                    'success' => true,
                ]);
    }

    /**
     * Test: Can acknowledge received data via API
     * RED Phase: This should fail initially
     */
    public function test_can_acknowledge_received_data_via_api()
    {
        $order = Order::factory()->create([
            'sync_status' => 'synced',
            'device_id' => $this->deviceId,
        ]);

        $response = $this->postJson('/api/v1/sync/acknowledge', [
            'device_id' => $this->deviceId,
            'order_ids' => [$order->id],
            'acknowledged_at' => now()->toIso8601String(),
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'acknowledged_count',
                    ],
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'acknowledged_count' => 1,
                    ],
                ]);
    }

    /**
     * Test: Can report conflicts via API
     * RED Phase: This should fail initially
     */
    public function test_can_report_conflicts_via_api()
    {
        $order = Order::factory()->create([
            'sync_status' => 'synced',
            'device_id' => $this->deviceId,
        ]);

        $response = $this->postJson('/api/v1/sync/conflict', [
            'device_id' => $this->deviceId,
            'conflicts' => [
                [
                    'type' => 'inventory_mismatch',
                    'local_order_id' => 'tablet-order-002',
                    'server_order_id' => $order->id,
                    'details' => [
                        'product_id' => 1,
                        'local_quantity' => 5,
                        'server_quantity' => 3,
                    ],
                ]
            ],
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'conflicts_reported',
                    ],
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                ]);
    }

    /**
     * Test: Can list unresolved conflicts via API
     * RED Phase: This should fail initially
     */
    public function test_can_list_unresolved_conflicts_via_api()
    {
        // Create some conflicts in the queue
        OrderSyncQueue::factory()->count(2)->create([
            'device_id' => $this->deviceId,
            'status' => 'conflict',
            'conflict_data' => json_encode(['type' => 'inventory_mismatch']),
        ]);

        $response = $this->getJson('/api/v1/sync/conflicts?device_id=' . $this->deviceId);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'conflicts' => [
                            '*' => [
                                'id',
                                'type',
                                'local_order_id',
                                'server_order_id',
                                'details',
                                'created_at',
                            ]
                        ],
                        'total_count',
                    ],
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_count' => 2,
                    ],
                ]);
    }

    /**
     * Test: Can resolve conflicts via API
     * RED Phase: This should fail initially
     */
    public function test_can_resolve_conflicts_via_api()
    {
        $conflict = OrderSyncQueue::factory()->create([
            'device_id' => $this->deviceId,
            'status' => 'conflict',
            'conflict_data' => json_encode(['type' => 'inventory_mismatch']),
        ]);

        $response = $this->putJson('/api/v1/sync/conflicts/' . $conflict->id, [
            'resolution' => 'use_server',
            'reason' => 'Server version is correct',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'conflict_id',
                        'resolution',
                        'status',
                    ],
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'conflict_id' => $conflict->id,
                        'resolution' => 'use_server',
                        'status' => 'completed',
                    ],
                ]);
    }

    /**
     * Test: Can dismiss conflicts via API
     * RED Phase: This should fail initially
     */
    public function test_can_dismiss_conflicts_via_api()
    {
        $conflict = OrderSyncQueue::factory()->create([
            'device_id' => $this->deviceId,
            'status' => 'conflict',
        ]);

        $response = $this->deleteJson('/api/v1/sync/conflicts/' . $conflict->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'conflict_id',
                        'status',
                    ],
                    'message',
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'conflict_id' => $conflict->id,
                        'status' => 'completed',
                    ],
                ]);
    }

    /**
     * Test: Upload validates required fields
     * RED Phase: This should fail initially
     */
    public function test_upload_validates_required_fields()
    {
        $response = $this->postJson('/api/v1/sync/upload', [
            'device_id' => '',
            'orders' => [],
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['device_id', 'orders']);
    }

    /**
     * Test: Sync status requires device_id
     * RED Phase: This should fail initially
     */
    public function test_sync_status_requires_device_id()
    {
        $response = $this->getJson('/api/v1/sync/status');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['device_id']);
    }
}