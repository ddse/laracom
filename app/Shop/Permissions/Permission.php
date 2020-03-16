<?php

namespace App\Shop\Permissions;

use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission/*extends LaratrustPermission*/
{
    protected $fillable = [
        'name',
        'display_name',
        'description'
    ];
}
