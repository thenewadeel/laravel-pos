<?php

namespace App\Exports;

use App\Models\Customer;
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

class CustomersExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithColumnFormatting //, WithDrawings //,WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Customer::all();
    }
    /**
     * @param Customer $customer
     */
    public function map($customer): array
    {

        // id,
        // name,
        // membership_number,
        // email,
        // phone,
        // address,
        // photo,
        // created_at,

        return [
            $customer->id,
            $customer->name,
            $customer->membership_number,
            $customer->email,
            $customer->phone,
            $customer->adress,
            // $customer->photo,
            $customer->type,
            Date::dateTimeToExcel($customer->created_at),
        ];
    }
    public function headings(): array
    {
        return [
            'id',
            'name',
            'membership_number',
            'email',
            'phone',
            'address',
            // 'photo',
            'type',
            'created_at',
            // 'id',
            // __('customer.FName'),
            // __('customer.LName'),
            // __('customer.Email'),
            // __('customer.Type'),
            // __('customer.Created At'),
        ];
    }
    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            // 'C' => NumberFormat::FORMAT_CURRENCY_EUR_INTEGER,
        ];
    }
    // public function drawings()
    // {
    //     $drawing = new Drawing();
    //     $drawing->setName('Logo');
    //     $drawing->setDescription('This is my logo');
    //     $drawing->setPath(public_path('/images/logo.png'));
    //     $drawing->setHeight(90);
    //     $drawing->setCoordinates('B3');

    //     return $drawing;
    // }
    // public function styles()
    // {
    //     return [
    //         // Style the first row as bold text.
    //         1    => ['font' => ['bold' => true]],
    //     ];
    // }
}
