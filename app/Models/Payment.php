<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'order_type',
        'payment_date',
    ];

    public function order()
    {
        return $this->morphTo(__FUNCTION__, 'order_type', 'order_id');
    }
}
