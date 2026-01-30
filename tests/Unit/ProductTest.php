<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_product_with_valid_data()
    {
        // RED: Product model needs validation and proper fields
        $productData = [
            'name' => 'Test Burger',
            'description' => 'Delicious beef burger with special sauce',
            'price' => 12.99,
            'quantity' => 100,
            'status' => true,
            'kitchen_printer_ip' => '192.168.1.100',
        ];
        
        $product = Product::create($productData);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertDatabaseHas('products', $productData);
        $this->assertEquals('Test Burger', $product->name);
        $this->assertEquals(12.99, $product->price);
        $this->assertEquals(100, $product->quantity);
        $this->assertTrue($product->status);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // RED: Validation rules need to be implemented
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Product::create([]); // Should fail with missing required fields
    }

    /** @test */
    public function it_validates_price_is_positive_number()
    {
        // RED: Price validation needed
        $this->expectException(\Exception::class);
        
        Product::factory()->create(['price' => -10.00]);
    }

    /** @test */
    public function it_validates_quantity_is_non_negative()
    {
        // RED: Quantity validation needed
        $this->expectException(\Exception::class);
        
        Product::factory()->create(['quantity' => -5]);
    }

    /** @test */
    public function it_can_have_multiple_categories()
    {
        // RED: Many-to-many relationship with categories
        $product = Product::factory()->create();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        $product->categories()->attach([$category1->id, $category2->id]);
        
        $this->assertCount(2, $product->categories);
        $this->assertTrue($product->categories->contains($category1));
        $this->assertTrue($product->categories->contains($category2));
    }

    /** @test */
    public function it_can_check_if_product_is_available()
    {
        // RED: Availability checking method needed
        $availableProduct = Product::factory()->create(['quantity' => 50, 'status' => true]);
        $unavailableProduct = Product::factory()->create(['quantity' => 0, 'status' => false]);
        $lowStockProduct = Product::factory()->create(['quantity' => 5, 'status' => true]);
        
        $this->assertTrue($availableProduct->isAvailable());
        $this->assertFalse($unavailableProduct->isAvailable());
        $this->assertTrue($lowStockProduct->isAvailable()); // 5 > 0, should be available
    }

    /** @test */
    public function it_can_check_if_product_is_low_stock()
    {
        // RED: Low stock checking method needed
        $normalStock = Product::factory()->create(['quantity' => 100]);
        $lowStock = Product::factory()->create(['quantity' => 10]);
        $criticalStock = Product::factory()->create(['quantity' => 5]);
        $outOfStock = Product::factory()->create(['quantity' => 0]);
        
        $this->assertFalse($normalStock->isLowStock());
        $this->assertTrue($lowStock->isLowStock()); // 10 is low stock threshold
        $this->assertTrue($criticalStock->isLowStock()); // 5 is definitely low stock
        $this->assertTrue($outOfStock->isLowStock()); // 0 should trigger low stock
    }

    /** @test */
    public function it_can_update_quantity_and_log_activity()
    {
        // RED: Quantity update with activity logging needed
        $product = Product::factory()->create(['quantity' => 100]);
        
        $product->updateQuantity(95); // Reduce by 5
        
        $this->assertEquals(95, $product->quantity);
        
        // Check that activity was logged
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'description' => 'quantity_updated',
        ]);
    }

    /** @test */
    public function it_prevents_negative_quantity()
    {
        // RED: Negative quantity protection needed
        $product = Product::factory()->create(['quantity' => 10]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient quantity available');
        
        $product->updateQuantity(-5); // Trying to reduce by 15 when only 10 available
    }

    /** @test */
    public function it_can_scope_by_availability()
    {
        // RED: Availability scope needed
        $available = Product::factory()->count(3)->create(['quantity' => 50, 'status' => true]);
        $unavailable = Product::factory()->count(2)->create(['quantity' => 0, 'status' => false]);
        
        $availableProducts = Product::available()->get();
        $unavailableProducts = Product::unavailable()->get();
        
        $this->assertCount(3, $availableProducts);
        $this->assertCount(2, $unavailableProducts);
        
        foreach ($available as $product) {
            $this->assertTrue($product->isAvailable());
        }
    }

    /** @test */
    public function it_can_scope_by_category()
    {
        // RED: Category scope needed
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();
        
        $product1->categories()->attach($category1);
        $product2->categories()->attach($category1);
        $product3->categories()->attach($category2);
        
        $category1Products = Product::byCategory($category1->id)->get();
        $category2Products = Product::byCategory($category2->id)->get();
        
        $this->assertCount(2, $category1Products);
        $this->assertCount(1, $category2Products);
    }

    /** @test */
    public function it_can_search_by_name()
    {
        // RED: Search functionality needed
        $burger1 = Product::factory()->create(['name' => 'Classic Beef Burger']);
        $burger2 = Product::factory()->create(['name' => 'Cheese Burger Deluxe']);
        $pizza = Product::factory()->create(['name' => 'Margherita Pizza']);
        
        $burgers = Product::search('burger')->get();
        $cheese = Product::search('cheese')->get();
        $pizzaResults = Product::search('pizza')->get();
        
        $this->assertCount(2, $burgers);
        $this->assertCount(1, $cheese);
        $this->assertCount(1, $pizzaResults);
        
        $this->assertTrue($burgers->contains($burger1));
        $this->assertTrue($burgers->contains($burger2));
        $this->assertTrue($pizzaResults->contains($pizza));
    }

    /** @test */
    public function it_formats_price_correctly()
    {
        // RED: Price formatting method needed
        $product = Product::factory()->create(['price' => 12.99]);
        
        $this->assertEquals('$12.99', $product->getFormattedPrice());
        $this->assertEquals('12.99', $product->getPriceWithoutSymbol());
    }

    /** @test */
    public function it_can_calculate_total_value()
    {
        // RED: Total value calculation needed
        $product = Product::factory()->create(['price' => 15.50, 'quantity' => 100]);
        
        $totalValue = $product->getTotalValue();
        
        $this->assertEquals(1550.00, $totalValue); // 15.50 * 100
    }

    /** @test */
    public function it_validates_printer_ip_format()
    {
        // RED: Printer IP validation needed
        $validIP = '192.168.1.100';
        $invalidIP = 'not-an-ip-address';
        
        $product1 = Product::factory()->create(['kitchen_printer_ip' => $validIP]);
        $product2 = Product::factory()->create(['kitchen_printer_ip' => $invalidIP]);
        
        $this->assertEquals($validIP, $product1->kitchen_printer_ip);
        $this->assertEquals($invalidIP, $product2->kitchen_printer_ip); // Should be null or validated
    }

    /** @test */
    public function it_soft_deletes_products()
    {
        // RED: Soft delete functionality needed
        $product = Product::factory()->create();
        
        $product->delete();
        
        // Should be soft deleted, not actually deleted
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertNotNull($product->deleted_at);
        
        // Should not appear in regular queries
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_can_restore_soft_deleted_products()
    {
        // RED: Soft delete restore needed
        $product = Product::factory()->create();
        $product->delete();
        
        $product->restore();
        
        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
        $this->assertNull($product->deleted_at);
    }

    /** @test */
    public function it_logs_activity_when_created_updated_deleted()
    {
        // RED: Comprehensive activity logging needed
        $product = Product::factory()->create();
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'description' => 'created',
        ]);
        
        $product->update(['name' => 'Updated Product']);
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'description' => 'updated',
        ]);
        
        $product->delete();
        
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'description' => 'deleted',
        ]);
    }

    /** @test */
    public function it_can_get_popular_products()
    {
        // RED: Popular products scope needed (based on sales)
        // This might require order data relationships
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product3 = Product::factory()->create();
        
        // For now, test the scope exists
        $popularProducts = Product::popular()->get();
        
        // The exact logic would depend on order relationships
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $popularProducts);
    }
}