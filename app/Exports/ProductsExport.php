<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithDrawings;

class ProductsExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithColumnFormatting  //WithDrawings,WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Product::all();
    }
    /**
     * @param Product $product
     */
    public function map($product): array
    {
        return [
            $product->name,
            $product->description,
            $product->categories->first()->name,
            $product->price,
            Date::dateTimeToExcel($product->created_at),
        ];
    }
    public function headings(): array
    {
        return [
            __('product.Name'),
            __('product.Description'),
            __('product.Category'),
            __('product.Price'),
            __('product.Created At'),
        ];
    }
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            // 'C' => NumberFormat::FORMAT_CURRENCY_EUR_INTEGER,
        ];
    }
}
