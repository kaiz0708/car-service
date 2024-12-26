<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Account extends Authenticatable

{
    use HasApiTokens, HasFactory;

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
