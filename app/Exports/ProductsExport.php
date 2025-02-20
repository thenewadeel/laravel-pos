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
        $cat = $product->categories->first();
        $cat = $cat ? $cat->name : 'unknown';
        return [
            $product->id,
            $product->name,
            $product->description,
            $cat,
            $product->price,
            $product->created_at ? Date::dateTimeToExcel($product->created_at) : '',
        ];
    }
    public function headings(): array
    {
        return [
            'id',
            'name',
            'description',
            'category',
            'price',
            'created_at',
            // ('name');
            // $table->text('description')->nullable();
            // $table->text('category')->nullable();
            // $table->string('image')->nullable();
            // $table->string('barcode')->unique()->nullable();
            // $table->decimal('price', 14, 2);
            // $table->integer('quantity')->default('1000');
            // $table->boolean('aval_status')->default(true);
            // $table->string('kitchen_printer_ip'
            // __('product.Name'),
            // __('product.Description'),
            // __('product.Category'),
            // __('product.Price'),
            // __('product.Created At'),
        ];
    }
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            // 'C' => NumberFormat::FORMAT_CURRENCY_EUR_INTEGER,
        ];
    }
}
