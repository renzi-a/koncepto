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
        'lat', 
        'lng',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Orders::class);
    }
    public function users()
    {
        return $this->hasMany(User::class, 'school_id');
    }

        public function school_admin()
    {
        return $this->hasOne(User::class, 'school_id')->where('role', 'school_admin');
    }

    public function teachers()
    {
        return $this->hasMany(User::class, 'school_id')->where('role', 'teacher');
    }

    public function students()
    {
        return $this->hasMany(User::class, 'school_id')->where('role', 'student');
    }

}

