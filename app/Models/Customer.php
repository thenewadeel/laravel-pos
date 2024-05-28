<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use AliBayat\LaravelCategorizable\Categorizable;

class Customer extends Model
{
    use Categorizable;
    protected $fillable = [
        'name',
        'membership_number',
        'email',
        'phone',
        'address',
        'photo',
    ];
    public function getphotoUrl()
    {
        return Storage::url($this->photo);
    }
}
