<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => 'nullable|integer|exists:customers,id',
            'shop_id' => 'nullable|integer|exists:shops,id',
            'amount' => 'nullable|numeric|min:0',
            'table_number' => 'nullable|string|max:3',
            'waiter_name' => 'nullable|string|max:191',
            'notes' => 'nullable|string|max:191',
            'state' => 'nullable|in:preparing,served,closed,wastage',
            'type' => 'nullable|in:dine-in,take-away,delivery',
            // 'user_id' => $this->user_id,
        ];
    }
}
