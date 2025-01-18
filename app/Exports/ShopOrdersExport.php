<?php



namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ShopOrdersExport implements WithHeadings, WithMapping
{
    private $orders;

    public function __construct($orders)
    {
        Log::alert('Exporting orders');
        $this->orders = $orders;
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->user->first_name,
            $order->receivedAmount(),
            $order->balance(),
            $order->discount,
            $order->total(),
            $order->customer->membership_number,
            $order->customer->name,
            $order->created_at ? Date::dateTimeToExcel($order->created_at) : '',
        ];
    }

    // public function map($invoice): array
    // {
    //     return [
    //         $invoice->invoice_number,
    //         $invoice->user->name,
    //         Date::dateTimeToExcel($invoice->created_at),
    //     ];
    // }

    public function headings(): array
    {
        return [
            'Order ID',
            'Cashier',
            'Cash / Payments',
            'Chit / Balance',
            'Discount',
            'Amount / Total',
            'Customer Membership No',
            'Customer Name',
            'Order Date',
        ];
    }
}
