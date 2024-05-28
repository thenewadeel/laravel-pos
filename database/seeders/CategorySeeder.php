<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use App\Models\Category;
use AliBayat\LaravelCategorizable\Category;

class CategorySeeder extends Seeder
{

    private $categories = [
        'Tea',
        'Sandwiches & More',
        'Pasta and More',
        'Dessert',
        'Cigarettes',
        'Fresh Items',
        'Beverages',
        'Other',
        'Sashimi',
        'Cake',
        'Hot Mazza',
        'Starter',
        'Sea Food',
        'Chefs Special Steak',
        'From The Bricks Oven',
        'Entrees',
        'Food',
        'BBQ',
        'Dry Ration',
        'Pakistani',
        'Main course',
        'Teppanyaki',
        'Delivery Charges ',
        'Appetizers',
        'Rice',
        'Brunch',
        'Hi-Tea',
        'Flat-Grilled Entrees',
        'Sushi',
        'Coffee',
        'Kids Corner',
        'Salads & Soups'
    ];
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->categories as $categoryName) {
            Category::firstOrCreate(['name' => $categoryName]);
            // \App\Models\Category::factory()->create([
            //     'name' => $categoryName
            // ]);
        }
    }
}
