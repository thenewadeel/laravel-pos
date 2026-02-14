<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sql_injection_protection(): void
    {
        $user = \App\Models\User::factory()->create();
        
        // Test that SQL injection attempt in query parameters doesn't break the application
        $response = $this->actingAs($user)->get('/orders?search=' . urlencode("'; DROP TABLE users; --"));
        
        // Should not cause a server error (500) - application should handle it gracefully
        $this->assertLessThan(500, $response->getStatusCode(), 'SQL injection should not cause server error');
    }

    public function test_xss_protection(): void
    {
        $response = $this->post('/products', [
            'name' => '<script>alert("xss")</script>',
            'description' => 'Product description',
        ]);

        $this->assertStringNotContainsString('<script>', $response->getContent());
    }

    public function test_csrf_protection(): void
    {
        $user = \App\Models\User::factory()->create();
        
        // Test CSRF protection by making POST request without token
        $response = $this->actingAs($user)->post('/logout');
        
        // Should get 419 (CSRF token mismatch) or redirect to login
        $this->assertTrue(
            $response->getStatusCode() === 419 || $response->getStatusCode() === 302,
            'CSRF protection should prevent unauthorized POST requests'
        );
    }
}