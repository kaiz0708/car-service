<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $nickname
 * @property string $email
 * @property string $password
 * @property int $role_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */

class Account extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    protected $fillable = [
        'nickname',
        'email',
        'password',
        'role_id',
        'created_at',
        'updated_at'
    ];

}
