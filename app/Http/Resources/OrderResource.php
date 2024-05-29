<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'POS_number' => $this->POS_number,
            'table_number' => $this->table_number,
            'waiter_name' => $this->waiter_name,
            'notes' => $this->notes,
            'state' => $this->state,
            'type' => $this->type,
            'customer_id' => $this->customer_id,
            'user_id' => $this->user_id,
            'shop_id' => $this->shop_id,
        ];
    }
}
