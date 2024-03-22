<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Traits\ListOf;

class InventoryItemController extends Controller
{
    use ListOf;

    protected function getModel(): string
    {
        return InventoryItem::class;
    }
}
