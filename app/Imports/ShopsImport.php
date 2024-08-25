<?php

namespace App\Imports;

use AliBayat\LaravelCategorizable\Category;
use App\Models\Shop;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;

class ShopsImport implements WithHeadingRow,   WithUpserts, OnEachRow //ToModel
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
        // $shop->id,
        // $shop->name,
        // $shop->description,
        // $cat,
        // $shop->printer_ip,
        // Date::dateTimeToExcel($shop->created_at),
        // dd($row);
        $shop =
            // new Shop([
            Shop::updateOrCreate([
                // Shop::firstOrCreate([
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
            ], [
                // 'category' => $row['category'],
                'printer_ip' => $row['printer_ip'],
                'created_at' => $row['created_at'],
                // ]);
            ]);
        $shop->categories()->sync(
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
        // Log::info("Shop imported: " . $shop->id);
        return $shop;
    }
}
