<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class OrderHistory extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_id',
        'user_id',
        'action_type', // Add action_type field
        'description',
        'created_at',
        'updated_at',
    ];

    protected $dates = ['created_at', 'updated_at']; // Ensure timestamps are recognized

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['order_id', 'user_id', 'action_type', 'description'])
            ->logOnlyDirty()
            ->useLogName('order_history')
            ->dontSubmitEmptyLogs();
    }

    // Optionally, add a method to generate a description based on action type and other data
    public function generateDescription(string $itemName = null, string $itemQty = null, string $printerIdentifier = null, string $paymentAmount = null, $POSNumber = null, $pdfFilePath = null): string
    {
        $description = '';
        // dd(func_get_args());
        switch ($this->action_type) {
            case 'created':
                $description = 'Order created by ' . $this->user->getFullname();
                break;

            case 'updated':
                $description = 'Order updated by ' . $this->user->getFullname();
                break;

            case 'cancelled':
                $description = 'Order cancelled by ' . $this->user->getFullname();
                break;

            case 'closed':
                $description = 'Order closed by ' . $this->user->getFullname();
                break;
            case 'pos-assigned':
                $description = 'POS Number ' . $POSNumber . ' assigned by ' . $this->user->getFullname();
                break;
            case 'payment-added':
                $description = 'Payment of ' . $paymentAmount . ' added by ' . $this->user->getFullname();
                break;

            case 'item-added':
                $description = 'Item ';
                if ($itemName && $itemQty != null) {
                    $description .= $itemName . ' x ' . $itemQty;
                } else {
                    $description .= 'unknown item';
                }
                $description .= ' added to order by ' . $this->user->getFullname();
                break;

            case 'item-removed':
                $description = 'Item ';
                if ($itemName && $itemQty != null) {
                    $description .= $itemName . ' x ' .  $itemQty;
                    // dd($itemName, $itemQty, $description);
                } else {
                    $description .= 'unknown item';
                }
                $description .= ' removed from order by ' . $this->user->getFullname();
                break;

            case 'discount-changed':
                $description = 'Discount changed by ' . $this->user->getFullname();
                break;

            case 'pos-print-printed':
                $description = 'POS Bill printed by ' . $this->user->getFullname();
                if ($printerIdentifier) {
                    $description .= ' on ' . $printerIdentifier;
                }
                break;

            case 'pdf-generated':
                $description = str_replace(storage_path('app/public/'), '', $pdfFilePath);
                break;
            case 'kot-printed':
                $description = 'KOT printed by ' . $this->user->getFullname();
                if ($itemName) {
                    $description .= ', for ' . $itemName;
                }
                if ($printerIdentifier) {
                    $description .= ' on ' . $printerIdentifier;
                }
                break;
            default:
                $description = 'Unknown action type';
        }

        if ($this->action_type != 'pdf-generated') {
            $description .= ' at ' . now()->format('d-M-y H:i');
        }

        return $description;
    }
    public static function boot()
    {
        parent::boot();
        // self::creating(function ($model) {
        //     $model->created_at = now();
        // });
    }

}
