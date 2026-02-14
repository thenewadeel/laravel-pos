<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTestSimple extends TestCase
{
    // Temporarily remove RefreshDatabase to focus on model functionality

    /** @test */
    public function it_can_create_a_product_with_valid_data()
    {
        // Create basic product without database
        $product = new Product([
            'name' => 'Test Burger',
            'description' => 'Delicious beef burger with special sauce',
            'price' => 12.99,
            'quantity' => 100,
            'aval_status' => true,
            'kitchen_printer_ip' => '192.168.1.100',
        ]);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Burger', $product->name);
        $this->assertEquals(12.99, $product->price);
        $this->assertEquals(100, $product->quantity);
        $this->assertTrue($product->aval_status);
    }

    /** @test */
    public function it_can_check_if_product_is_available()
    {
        $availableProduct = new Product(['quantity' => 50, 'aval_status' => true]);
        $unavailableProduct = new Product(['quantity' => 0, 'aval_status' => false]);
        $lowStockProduct = new Product(['quantity' => 5, 'aval_status' => true]);
        
        $this->assertTrue($availableProduct->isAvailable());
        $this->assertFalse($unavailableProduct->isAvailable());
        $this->assertTrue($lowStockProduct->isAvailable()); // 5 > 0, should be available
    }

    /** @test */
    public function it_can_check_if_product_is_low_stock()
    {
        $normalStock = new Product(['quantity' => 100]);
        $lowStock = new Product(['quantity' => 10]);
        $criticalStock = new Product(['quantity' => 5]);
        $outOfStock = new Product(['quantity' => 0]);
        
        $this->assertFalse($normalStock->isLowStock());
        $this->assertTrue($lowStock->isLowStock()); // 10 is low stock threshold
        $this->assertTrue($criticalStock->isLowStock()); // 5 is definitely low stock
        $this->assertTrue($outOfStock->isLowStock()); // 0 should trigger low stock
    }

    /** @test */
    public function it_formats_price_correctly()
    {
        $product = new Product(['price' => 12.99]);
        
        $this->assertEquals('$12.99', $product->getFormattedPrice());
        $this->assertEquals('12.99', $product->getPriceWithoutSymbol());
    }

    /** @test */
    public function it_can_calculate_total_value()
    {
        $product = new Product(['price' => 15.50, 'quantity' => 100]);
        
        $totalValue = $product->getTotalValue();
        
        $this->assertEquals(1550.00, $totalValue); // 15.50 * 100
    }

    /** @test */
    public function it_maps_status_attribute_correctly()
    {
        $product = new Product(['aval_status' => true]);
        $this->assertTrue($product->status);
        
        $product->status = false;
        $this->assertFalse($product->aval_status);
    }
}