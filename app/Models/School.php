<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Orders;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_name',
        'school_email',
        'address',
        'image',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function orders()
{
    return $this->hasMany(Orders::class);
}
}
