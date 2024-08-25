<?php

namespace App\Exports;

use App\Models\Shop;
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

class ShopsExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithColumnFormatting  //WithDrawings,WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Shop::all();
    }
    /**
     * @param Shop $shop
     */
    public function map($shop): array
    {
        // 'id',
        // 'name',
        // 'description',
        // 'category',
        // 'printer_ip',
        // 'created_at',

        $cat = $shop->categories->first();
        $cat = $cat ? $cat->name : 'unknown';
        return [
            $shop->id,
            $shop->name,
            $shop->description,
            $cat,
            $shop->printer_ip,
            Date::dateTimeToExcel($shop->created_at),
        ];
    }
    public function headings(): array
    {
        return [
            // id,
            // name,
            // description,
            // image,
            // surcharge_percentage,
            // created_at,
            // updated_at,
            // printer_ip

            'id',
            'name',
            'description',
            'category',
            'printer_ip',
            'created_at',
        ];
    }
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            // 'C' => NumberFormat::FORMAT_CURRENCY_EUR_INTEGER,
        ];
    }
}
