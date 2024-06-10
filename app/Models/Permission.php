<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    use HasFactory;

    public static function defaultPermissions(): array
    {
        $modules = [
            'users',
            'roles',
            'permissions',
        ];
        $permissions = [];

        foreach ($modules as $module) {
            $permissions = array_merge($permissions, [
                "view_$module",
                "add_$module",
                "edit_$module",
                "delete_$module",
            ]);
        }

        return $permissions;
    }
}
