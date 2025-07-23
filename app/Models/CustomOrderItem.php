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
        'gathered',
    ];
    protected $casts = [
        'gathered' => 'boolean',
    ];

    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class);
    }

}