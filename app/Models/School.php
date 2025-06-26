<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'user_id',
        'school_name',
        'school_email',
        'address',
        'image',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Orders::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

}

