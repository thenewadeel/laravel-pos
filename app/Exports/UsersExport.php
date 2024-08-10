<?php

namespace App\Exports;

use App\Models\User;
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

class UsersExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithColumnFormatting //, WithDrawings //,WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::all();
    }
    /**
     * @param User $user
     */
    public function map($user): array
    {

        // ('first_name');
        // $table->string('last_name');
        // $table->enum('type', ['admin', 'cashier', 'accountant', 'chef', 'stockBoy'])->default('cashier');
        // $table->string('email')->unique();

        return [
            // $user->id,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->type,
            Date::dateTimeToExcel($user->created_at),
        ];
    }
    public function headings(): array
    {
        return [
            // 'id',
            __('user.FName'),
            __('user.LName'),
            __('user.Email'),
            __('user.Type'),
            __('user.Created At'),
        ];
    }
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            // 'C' => NumberFormat::FORMAT_CURRENCY_EUR_INTEGER,
        ];
    }
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('/images/logo.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('B3');

        return $drawing;
    }
    // public function styles()
    // {
    //     return [
    //         // Style the first row as bold text.
    //         1    => ['font' => ['bold' => true]],
    //     ];
    // }
}
