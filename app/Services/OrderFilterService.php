<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderFilterService
{
    public function applyFilters($query, Request $request)
    {
        // Log::debug('OrderFilterService:: Starting applyFilters', ['request' => $request->all()]);

        if (!$this->hasAnyFilters($request)) {
            Log::debug('OrderFilterService:: No filters applied');
            return $query->whereBetween('created_at', [now()->startOfMonth(), now()]);
        }

        $filters = $this->getValidFilters($request);
        Log::debug("FILTERS");
        Log::debug($filters);

        if ($request->has('start_date') && $request->query('start_date') != null) {
            $this->applyDateFilters($query, $request, $filters);
        }

        $this->applyConditionalFilters($query, $filters);

        Log::debug('OrderFilterService:: Ending applyFilters', ['modifiedQuery' => $query->toSql(), 'modifiedQueryBindings' => $query->getBindings()]);

        return $query;
    }
    // Order Model Scopes
    // - order_status [open|closed]
    // - customer_ids >
    // - customer_name
    // - order_takers >
    // - shop_ids >
    // - cashiers >
    // - item_ids >
    // - item_name

    private function hasAnyFilters(Request $request): bool
    {
        return $request->hasAny([
            'start_date',
            'end_date',
            'order_type',
            'order_status',
            'customer_id',
            'customer_name',
            'order_taker',
            'order_takers',
            'table_number',
            'waiter_name',
            'shop_ids',
            'cashiers',
            // 'item_ids',
            'item_name',
        ]);
    }

    private function getValidFilters(Request $request): array
    {
        // Log::debug('OrderFilterService:: Filtering orders with:', $request->query());
        $filters = [
            'pos_number' => $request->query('pos_number'),
            'start_date' => $request->query('start_date'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'type' => $request->query('type'),
            'order_status' => $request->query('order_status'),
            'customer_id' => $request->query('customer_id'),
            'customer_name' => $request->query('customer_name'),
            'order_taker' => $request->query('order_taker'),
            'order_takers' => $request->query('order_takers'),
            'table_number' => $request->query('table_number'),
            'waiter_name' => $request->query('waiter_name'),
            'shop_ids' => $request->query('shop_ids'),
            'cashiers' => $request->query('cashiers'),
            // 'item_ids' => $request->query('item_ids'),
            'item_name' => $request->query('item_name'),
        ];

        $filters = array_filter($filters, function ($value) {
            return $value !== null;
        });

        Log::debug('OrderFilterService:: Filtered orders with:', $filters);

        return $filters;
    }

    private function applyDateFilters($query, Request $request, array $filters)
    {
        // Log::debug('OrderFilterService:: Applying date filters');
        $hasStartDate = $request->has('start_date') && $request->query('start_date') != null;
        $hasEndDate = $request->has('end_date') && $request->query('end_date') != null;

        if ($hasStartDate && $hasEndDate) {
            $query->whereBetween('created_at', [$request->query('start_date'), $request->query('end_date')]);
            // $query->date_between($filters['start_date'], $filters['end_date'] ?: now()->endOfDay());
        } else if ($hasStartDate) {
            $query->whereDate('created_at', $request->query('start_date'));
        } else {
            $query->whereDate('created_at', now()->startOfDay());
            // $query->start_date($filters['start_date']);
        }

        Log::debug('OrderFilterService:: Applied date filters', ['modifiedQuery' => $query->toSql(), 'modifiedQueryBindings' => $query->getBindings()]);
    }

    private function applyConditionalFilters($query, array $filters)
    {
        $filterMap = [
            'pos_number' => 'pos_number',
            'type' => 'order_type',
            'order_status' => 'order_status',
            'customer_ids' => 'customer_ids',
            'customer_name' => 'customer_name',
            'order_taker' => 'order_taker',
            'order_takers' => 'order_takers',
            'table_number' => 'table_number',
            'waiter_name' => 'waiter_name',
            'shop_ids' => 'shop_ids',
            'cashiers' => 'cashiers',
            // 'item_ids' => 'item_ids',
            'item_name' => 'item_name',
        ];

        foreach ($filterMap as $requestKey => $method) {
            if (isset($filters[$requestKey])) {
                $query->$method($filters[$requestKey]);
            }
        }
    }
}
