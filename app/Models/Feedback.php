<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'phone',

        'order_id',
        'user_id',

        'presentation_and_plating',
        'taste_and_quality',

        'friendliness',
        'service',
        'knowledge_and_recommendations',

        'atmosphere',
        'cleanliness',

        'overall_experience',

        'comments',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shop()
    {
        return $this->belongsToThrough(Shop::class, Order::class);
    }

    public function customer()
    {
        return $this->belongsToThrough(Customer::class, Order::class);
    }
}
