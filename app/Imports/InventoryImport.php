<?php

namespace App\Imports;

use App\Models\Inventory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToModel;

class InventoryImport implements ToModel, ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        //
        logger("importing collection....");
        logger($collection);
    }

    public function model(array $row)
    {
        // Head	Ser	Item Name	Per Kg/Pack/Box	Bal	Amount Rs.
        // storeSheet.xlsx

        logger("importing file....");
        logger($row);
        return new Inventory([
            'name' => $row[0],
            'type' => $row[1],
            'unit' => $row[2],
            'quantity' => $row[3],
            'price' => $row[4],
        ]);
    }
    public function headingRow(): int
    {
        return 3;
    }
}
