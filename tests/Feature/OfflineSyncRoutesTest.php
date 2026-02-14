<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineSyncRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['type' => 'admin']);
    }

    /** @test */
    public function tablet_order_route_requires_authentication()
    {
        $response = $this->get('/tablet-order');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_tablet_order_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/tablet-order');
        
        $response->assertStatus(200)
            ->assertSee('Tablet Order Entry')
            ->assertSee('tablet-order-component');
    }

    /** @test */
    public function sync_status_route_requires_authentication()
    {
        $response = $this->get('/sync-status');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_sync_status_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/sync-status');
        
        $response->assertStatus(200)
            ->assertSee('Sync Status Dashboard')
            ->assertSee('sync-status-component');
    }

    /** @test */
    public function conflict_resolution_route_requires_authentication()
    {
        $response = $this->get('/conflict-resolution');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_access_conflict_resolution_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/conflict-resolution');
        
        $response->assertStatus(200)
            ->assertSee('Conflict Resolution')
            ->assertSee('conflict-resolution-component');
    }

    /** @test */
    public function routes_use_admin_layout()
    {
        $response = $this->actingAs($this->user)
            ->get('/tablet-order');
        
        $response->assertStatus(200)
            ->assertViewIs('offline-sync.tablet-order');
    }

    /** @test */
    public function cashier_can_access_tablet_order_page()
    {
        $cashier = User::factory()->create(['type' => 'cashier']);
        
        $response = $this->actingAs($cashier)
            ->get('/tablet-order');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_all_sync_pages()
    {
        $routes = [
            '/tablet-order',
            '/sync-status',
            '/conflict-resolution',
        ];
        
        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)->get($route);
            $response->assertStatus(200);
        }
    }
}
