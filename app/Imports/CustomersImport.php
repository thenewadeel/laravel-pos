<?php

namespace App\Imports;

use AliBayat\LaravelCategorizable\Category;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;

class CustomersImport implements WithHeadingRow,   WithUpserts, OnEachRow //ToModel
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
        // $customer->id,
        // $customer->name,
        // $customer->membership_number,
        // $customer->email,
        // $customer->phone,
        // $customer->adress,
        // // $customer->photo,
        // $customer->type,
        // Date::dateTimeToExcel($customer->created_at),
        // dd($row);
        $customer =
            // new Customer([
            Customer::updateOrCreate([
                // Customer::firstOrCreate([
                'id' => $row['id'],
                'name' => $row['name'],
                'membership_number' => $row['membership_number'],
            ], [
                // 'photo' => $row['photo'],
                'phone' => $row['phone'],
                'address' => $row['address'],
                'type' => $row['type'],
                'created_at' => $row['created_at'],
                // ]);
            ]);
        // $customer->categories()->sync(
        //     // setRelation(
        //     // attachCategory(
        //     // $category->id
        //     // 'category',
        //     // new Category([
        //     // $category =
        //     Category::updateOrCreate([
        //         // [
        //         'name' => $row['category']
        //     ], [
        //         'type' => 'customer'
        //         // ]
        //     ])
        // );
        // Log::info("Customer imported: " . $customer->id);
        return $customer;
    }
}
