<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait ListOf
{
    /**
     * Returns a collection of all records from the associated model.
     *
     * @param \Illuminate\Http\Request $request (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listOf(Request $request = null)
    {
        $modelClass = $this->getModel(); // Delegate to controller implementation
        $query = $modelClass::query();

        if ($request && $request->search) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        return $query->get();
    }
}
