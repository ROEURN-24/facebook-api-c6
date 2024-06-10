<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    use HasFactory;

    public const ROLE_ADMIN = 'Admin';

    public const ROLE_USER = 'User';

    public static function allRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_USER,
        ];
    }
}
