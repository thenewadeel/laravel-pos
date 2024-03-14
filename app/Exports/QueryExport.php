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

class QueryExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithDrawings
{
    public $queryObj;
    public $exportColumns; // Add a property to store selected columns
    public $columnFormatting; // Add a property to store formatting rules
    public function __construct($queryObj, array $exportColumns = [], array $columnFormatting = [])
    {
        $this->queryObj = $queryObj;
        $this->exportColumns = $exportColumns;
        $this->columnFormatting = $columnFormatting;
    }

    public function collection()
    {
        return $this->queryObj->get();
    }


    /**
     * Map the row to the desired array. This will be sent from the controller.
     *
     * @param mixed $row The current row being mapped.
     * @return array The mapped array.
     */
    public function map($order): array
    {
        // Add your mapping logic here
        // Example:
        return [
            $order->id,
            $order->user->first_name, // Assuming user relationship on order
            $order->receivedAmount(),
            $order->balance(),
            $order->discount, // Assuming discount attribute/method exists
            $order->total(),
            $order->customer->membership_number,
            $order->customer->name,
            $order->created_at,
        ];
    }

    public function headings(): array
    {
        if ($this->queryObj->get()->isNotEmpty()) {
            if (empty($this->exportColumns)) {
                return array_keys($this->queryObj->get()->first()->toArray());
            } else {
                $selectedColumns = [];
                foreach ($this->exportColumns as $column) {
                    if (array_key_exists($column, $this->queryObj->get()->first()->toArray())) {
                        $selectedColumns[] = $column;
                    }
                }
                return $selectedColumns;
            }
        }

        return [];
    }

    public function columnFormats(): array
    {
        $formats = [];
        if (!empty($this->columnFormatting)) {
            foreach ($this->columnFormatting as $column => $format) {
                $formats[$column] = $format;
            }
        }
        return $formats;
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
}
