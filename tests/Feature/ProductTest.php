<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_example(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee("table");
    }
}
