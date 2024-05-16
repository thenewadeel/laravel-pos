<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'percentage', 'method', 'amount'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product')
            ->withTimestamps()
            ->withPivot('name');
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'discount_order')
            ->withTimestamps();
        // ->withPivot('name');
    }
    public function apply($value)
    {
        // $table->string('name');
        // $table->decimal('percentage', 5, 2)->unsigned()->min(0)->max(100);
        // $table->decimal('amount', 10, 2)->unsigned()->min(0);
        // $table->enum('method', ['NATURAL', 'REVERSE'])->default('NATURAL');
        // $table->enum('type', ['DISCOUNT', 'CHARGES'])->
        $increment = $this->amount;
        if ($this->type == 'DISCOUNT') {
            if ($this->method == 'NATURAL') {
                $result = ($value - ($value * $this->percentage / 100)) - $increment;
            } else {
                $value = ($value - $increment);
                $result = ($value - ($value  * $this->percentage / 100));
            }
        } else {
            if ($this->method == 'NATURAL') {
                $result = ($value + ($value * $this->percentage / 100)) + $increment;
            } else {
                $value = ($value + $increment);
                $result = ($value + ($value * $this->percentage / 100));
            }
        }
        return $result;
    }
}
