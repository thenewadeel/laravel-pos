<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\User;

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

        if ($modelClass === Shop::class) {
            $u = User::with('shops')->find(auth()->id());
            $shopsArray = $u->shops()->pluck('shops.id')->toArray();


            $query->with('categories')->whereIn('id', $shopsArray);
        }



        return $query->get();
    }
}
