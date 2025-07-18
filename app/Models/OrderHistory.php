<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $table = 'order_history';

    protected $fillable = [
        'original_order_id',
        'user_id',
        'reason',
        'status',
        'items',
        'order_type',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function customOrder()
{
    return $this->belongsTo(\App\Models\CustomOrder::class, 'custom_order_id');
}
public function orderDetails()
{
    return $this->hasMany(OrderDetail::class);
}

}
