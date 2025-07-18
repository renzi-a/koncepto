<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'Orderdate',
        'Shipdate',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

public function orderDetails()
{
    return $this->hasMany(OrderDetail::class, 'order_id');
}


    public function customOrder()
    {
        return $this->hasOne(CustomOrder::class, 'id', 'custom_order_id');
    }
    public function items()
{
    return $this->hasMany(OrderDetail::class, 'order_id');
}
public function payment()
{
    return $this->morphOne(Payment::class, 'order');
}

}
