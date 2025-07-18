<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'category',
        'unit',
        'quantity',
        'photo',
        'description',
        'price',
        'total_price',
    ];


    public function order()
    {
        return $this->belongsTo(CustomOrder::class);
    }
}