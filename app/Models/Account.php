<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $nickname
 * @property string $email
 * @property string $password
 * @property int $role_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Role $role
 * @method static Account find(int $id)
 * @method static \Illuminate\Database\Eloquent\Builder|Account where(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereIn(string $column, array $values)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereNotNull(string $column)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereNull(string $column)
 */

class Account extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

}
