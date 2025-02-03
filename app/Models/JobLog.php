<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_name',
        'job_id',
        'payload',
        'status',
        'progress',
        'category'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeOfUser($query, $user)
    {
        return $query->where('user_id', $user);
    }
    public function scopeOfCategory($query, $cat)
    {
        return $query->where('category', $cat);
    }
}
