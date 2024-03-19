<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_unauth_goes_to_login(): void
    {
        $response = $this->get('/products');

        $response->assertRedirect('/login');
    }
}
