<?php

namespace App\Imports;

use AliBayat\LaravelCategorizable\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;

class ProductsImport implements WithHeadingRow,   WithUpserts, OnEachRow //ToModel
{
    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'id';
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    // public function model(array $row)
    // {
    public function onRow(Row $row)
    {
        // dd($row);
        // $categoryName = $row['category'];
        // $category = Category::firstOrCreate(
        //     [
        //         'name' => $categoryName,
        //         'type' => 'product'
        //     ]
        // );
        // dd($row);
        $product =
            // new Product([
            Product::updateOrCreate([
                // Product::firstOrCreate([
                'id' => $row['id'],
                'name' => $row['name'],
            ], [
                'description' => $row['description'],
                // 'category' => $row['category'],
                'price' => $row['price'],
                'created_at' => $row['created_at'],
                // ]);
            ]);
        $product->categories()->sync(
            // setRelation(
            // attachCategory(
            // $category->id
            // 'category',
            // new Category([
            // $category =
            Category::updateOrCreate([
                // [
                'name' => $row['category']
            ], [
                'type' => 'product'
                // ]
            ])
        );
        // Log::info("Product imported: " . $product->id);
        return $product;
    }
}
