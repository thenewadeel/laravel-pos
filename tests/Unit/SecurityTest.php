<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityTest extends TestCase
{
    public function test_sql_injection_protection(): void
    {
        $response = $this->get('/search?q=' . urlencode("'; DROP TABLE users; --"));
        
        $this->assertDatabaseCount('users', 0);
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
        $response = $this->post('/logout');
        
        $response->assertStatus(419);
    }
}