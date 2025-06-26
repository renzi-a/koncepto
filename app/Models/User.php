<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

// app/Models/User.php
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'cp_no',
        'role',
        'password',
        'school_id',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function schools()
    {
        return $this->hasMany(School::class);
    }

    public function orders()
    {
        return $this->hasMany(Orders::class);
    }
}
