<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Storage;
// use App\Models\Category;
use AliBayat\LaravelCategorizable\Category;
use Illuminate\Support\Facades\DB;
use App\Models\Shop;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        $directory = [];
        $tokenItems = [];

        // foreach ($directory as $productSpecimen) {
        //     // dd($productSpecimen);

        //     Product::updateOrCreate([
        //         'name' => $productSpecimen['name'],
        //         // 'description' => $productSpecimen->make,
        //         // 'category' => $productSpecimen->category,
        //         // 'image' => "images/{$imageName}",
        //         // 'barcode' => $faker->ean13(),
        //         'price' => $productSpecimen['price'],
        //         'quantity' => '999',
        //         'aval_status' => true,
        //     ]);
        // }

        // $category = Category::updateOrCreate([
        //     'name' => 'Tokenised Items',
        // ]);

        // $shopNames = ["TokenShop1", "TokenShop2", "TokenShop3", "TokenShop4", "TokenShop5", "TokenShop6"];
        // foreach ($shopNames as $shopName) {
        //     $shop = Shop::updateOrCreate([
        //         'name' => $shopName,
        //         'description' => $shopName,
        //         'image' => '',
        //     ]);
        //     $shop->categories()->attach($category);
        // }
        // foreach ($tokenItems as $productSpecimen) {
        //     // dd($productSpecimen);

        //     $product = Product::updateOrCreate([
        //         'name' => $productSpecimen['name'],
        //         'description' => $productSpecimen['description'],
        //         'price' => $productSpecimen['price'],
        //         'quantity' => '999',
        //         'aval_status' => true,
        //     ]);


        //     DB::insert('insert into category_products (category_id, product_id) values (?, ?)', [$category->id, $product->id]);
        // }



        DB::table('products')->delete();
        // DB::table('category_products')->delete();
        DB::table('categories')->delete();

        $csvFilePath = base_path('seedData/products.csv');
        if (file_exists($csvFilePath)) {
            $products = array_map('str_getcsv', file($csvFilePath));
            $header = array_shift($products);
            foreach ($products as $product) {
                $data = array_combine($header, $product);
                $categoryName = $data['categoryName'];
                $category = Category::firstOrCreate(
                    [
                        'name' => $categoryName,
                        'type' => 'product'
                    ]
                );
                $product = Product::updateOrCreate([
                    'name' => $data['name'],
                    'price' => $data['price'],
                ]);
                // DB::table('category_products')->insert([
                //     'category_id' => $category->id,
                //     'product_id' => $product->id,
                // ]);

                $product->attachCategory($category);
                // $product->categories()->attach($category);
            }
        }
    }
}
// {"name":"971 Gajjar Halwa Full","price":1500,"category":"Dessert","make":"Pakistani"}


// $table->string('name');
// $table->text('description')->nullable();
// $table->text('category')->nullable();
// $table->string('image')->nullable();
// $table->string('barcode')->unique();
// $table->decimal('price', 8, 2);
// $table->boolean('status')->default(true);

// 'name',
//         'description',
//         'image',
//         'barcode',
//         'price',
//         'quantity',
//         'status',
//         'category'
